<?php

namespace App\Modules\User\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Modules\User\Requests\UserCreateRequest;
use App\Modules\User\Requests\UserUpdateRequest;
use App\Modules\User\Requests\SwitchLanguageRequest;
use App\Modules\User\Models\User;
use App\Modules\Role\Models\Role;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        // Получаем параметры фильтрации
        $search = $request->input('search');
        $roleId = $request->input('role_id', 'all');
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $status = $request->input('status', 'all');
        
        // Валидируем количество на странице
        $validPerPage = in_array($perPage, [5, 10, 25, 50]) ? (int)$perPage : 10;
        
        // Строим запрос с фильтрами
        $query = User::with('role')
            ->when($search, function($q) use ($search) {
                return $q->where(function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($roleId !== 'all', function($q) use ($roleId) {
                return $q->where('role_id', $roleId);
            })
            ->when($status !== 'all', function($q) use ($status) {
                return $q->where('is_active', $status === 'active');
            });

        // Применяем сортировку
        $validSortColumns = ['name', 'email', 'created_at', 'last_login_at'];
        $validSortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
        
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $validSortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate($validPerPage)->withQueryString();
        $roles = Role::orderBy('name')->get();
        
        // Статистика
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();

        return view('user::index', compact(
            'users', 
            'roles', 
            'search', 
            'roleId', 
            'perPage', 
            'sortBy', 
            'sortOrder',
            'status',
            'totalUsers',
            'activeUsers',
            'inactiveUsers'
        ));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('user::create', compact('roles'));
    }

    public function store(UserCreateRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
            'is_lang' => $validated['is_lang'],
        ]);

        return redirect()->route('admin.users')->with('success', 'Пользователь успешно добавлен');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('user::edit', compact('user', 'roles'));
    }

    public function update(User $user, UserUpdateRequest $request)
    {
        $validated = $request->validated();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'is_lang' => $validated['is_lang'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', 'Данные пользователя обновлены');
    }

    /**
     * Переключение языка интерфейса
     * @param SwitchLanguageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLanguage(SwitchLanguageRequest $request)
    {
        $locale = $request->input('locale');
        $languageService = app(\App\Admin\Services\LanguageService::class);
        
        if ($languageService->setLocale($locale)) {
            // Очищаем кэш и куки предыдущего языка
            $languageService->clearAllLanguageData(auth()->id());
            
            // Если это AJAX запрос, возвращаем JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Язык успешно изменен',
                    'locale' => $locale
                ]);
            }
            
            // Если обычный запрос, редирект с сообщением
            return back()->with('success', 'Язык успешно изменен');
        }
        
        // Если это AJAX запрос
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось изменить язык'
            ], 422);
        }
        
        return back()->with('error', 'Не удалось изменить язык');
    }

    public function destroy(User $user)
    {
        // Защита от удаления системных пользователей
        if ($user->is_system) {
            return redirect()->route('admin.users')->with('error', 'Невозможно удалить системного пользователя');
        }
        
        // Защита от удаления самого себя
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'Вы не можете удалить свой аккаунт');
        }
        
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Пользователь удален');
    }
}