<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMovement extends Model
{
    protected $table = 'product_movements';

    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'reference',
    ];

    // Movement type constants
    public const TYPE_PURCHASE = 0;
    public const TYPE_PURCHASE_RETURN = 1;
    public const TYPE_TRANSFER = 2;
    public const TYPE_SALE = 3;
    public const TYPE_SALE_RETURN = 4;
    public const TYPE_GRN_RETURN = 5; // <-- new type for GRN return

    /**
     * Create a product movement record (helper).
     *
     * @param int $productId
     * @param int $movementType
     * @param float $quantity
     * @param string|null $reference
     * @return static
     */
    public static function record(int $productId, int $movementType, float $quantity, ?string $reference = null)
    {
        return self::create([
            'product_id' => $productId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'reference' => $reference,
        ]);
    }
}
