# Plan: Remove Customer Functionality

## Migrations (delete)
1. `2025_03_12_000001_create_catalog_customers_type_table.php`
2. `2025_03_12_000002_create_catalog_customers_table.php`

## Models (delete)
1. `app/Modules/Catalog/Models/Customer.php`
2. `app/Modules/Catalog/Models/CustomerType.php`
3. `app/Modules/Catalog/Models/CustomerTrait.php`

## Controllers (delete)
1. `app/Modules/Catalog/Controllers/CustomerController.php`
2. `app/Modules/Catalog/Controllers/CustomerTypeController.php`
3. `app/Modules/Catalog/Controllers/Api/CustomersController.php`
4. `app/Modules/Catalog/Controllers/Api/CustomerTypeController.php`

## Views (delete)
1. Entire directory `app/Modules/Catalog/views/customers/`

## Config (update)
- Remove customer menu entry from `config.php`

## Routes (update)
- Remove customer routes from `routes/admin.php`
- Remove customer routes from `routes/api.php`
- Remove customer controller imports from both route files

## Foreign Keys / Table Columns (migration)
- In `catalog_baskets` table: remove `customer_id` column

## Models (update)
- `CatalogBasket.php`: remove `customer()` relation, `customer_id` from fillable

## Requests (update)
- `UpdateBasketRequest.php`: remove `customer_id` validation
- `CreateBasketRequest.php`: remove `customer_id` validation
- `OrdersEditRequest.php`: remove `customer_id` validation
- `OrdersCreateRequest.php`: remove `customer_id` validation

## Controllers (update)
- `BasketController.php`: remove customer filtering and handling

## Blade Views (update)
- `basket/index.blade.php`: remove customer filter dropdown
- `basket/edit.blade.php`: remove customer select field and note
- `orders/create.blade.php`: remove customer select field
- `orders/edit.blade.php`: remove customer select field
- `orders/index.blade.php`: remove customer display

---
**Note:** Order table's `customer_id` references `users` table (not catalog_customers), so it stays unchanged.