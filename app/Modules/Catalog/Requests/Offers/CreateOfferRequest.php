use App\Modules\Catalog\Models\Offer;
use App\Modules\Catalog\Models\PriceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Запрос на создание предложения товара
 *
 * Валидация данных при создании нового предложения товара.
 */
class CreateOfferRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();