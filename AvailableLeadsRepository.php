<?php

namespace App\Repositories\Odin;

use App\Contracts\Repositories\Odin\AvailableLeadsRepositoryContract;
use App\Models\Odin\Address;
use App\Models\Odin\AvailableLead;
use App\Models\Odin\Consumer;
use App\Models\Odin\ConsumerProduct;
use App\Models\Odin\IndustryService;
use App\Models\Odin\Product;
use App\Models\Odin\ServiceProduct;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AvailableLeadsRepository implements AvailableLeadsRepositoryContract
{
    /**
     * @inheritDoc
     */
    public function truncate(): void
    {
        AvailableLead::query()->truncate();
    }

    /**
     * @inheritDoc
     */
    public function getAvailableLeadsForIndustryService(int $industryService, string $period): Collection
    {
        return ConsumerProduct::query()
            ->select(
                [
                    Address::TABLE . '.' . Address::FIELD_ID . ' as location_id',
                    ConsumerProduct::TABLE . '.' . ConsumerProduct::FIELD_STATUS . ' as status',
                    DB::raw("count(*) as count"),
                    DB::raw("IF("
                        .Consumer::TABLE . '.' . Consumer::FIELD_CLASSIFICATION." = '".Consumer::CLASSIFICATION_VERIFIED_EMAIL_ONLY."' or "
                        .Consumer::TABLE . '.' . Consumer::FIELD_CLASSIFICATION." = '".Consumer::CLASSIFICATION_VERIFIED_PHONE_VIA_SMS."' or "
                        .Consumer::TABLE . '.' . Consumer::FIELD_CLASSIFICATION." = '".Consumer::CLASSIFICATION_VERIFIED_PHONE_VIA_CALL."', 1, 0) as `verified`"),
                    ServiceProduct::FIELD_INDUSTRY_SERVICE_ID . ' as service_id'
                ]
            )
            ->join(ServiceProduct::TABLE, ServiceProduct::TABLE . '.' . ServiceProduct::FIELD_ID, ConsumerProduct::TABLE . '.' . ConsumerProduct::FIELD_SERVICE_PRODUCT_ID)
            ->join(Product::TABLE, function(JoinClause $join) {
                return $join
                    ->on(Product::TABLE . '.' . Product::FIELD_ID, '=', ServiceProduct::FIELD_PRODUCT_ID)
                    ->where(Product::FIELD_NAME, 'Lead');
            })
            ->join(IndustryService::TABLE, function(JoinClause $join) use ($industryService) {
                return $join
                    ->on(IndustryService::TABLE . '.' . IndustryService::FIELD_ID, '=', ServiceProduct::FIELD_INDUSTRY_SERVICE_ID)
                    ->where(ServiceProduct::FIELD_INDUSTRY_SERVICE_ID, $industryService);
            })
            ->join(Consumer::TABLE, Consumer::TABLE . '.' . Consumer::FIELD_ID, ConsumerProduct::FIELD_CONSUMER_ID)
            ->join(Address::TABLE, Address::TABLE . '.' . Address::FIELD_ID, ConsumerProduct::FIELD_ADDRESS_ID)
            ->where(ConsumerProduct::TABLE . '.' . ConsumerProduct::FIELD_CREATED_AT , '>=', $period)
            ->groupBy(['location_id', 'status', 'verified'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function save(array $data): void
    {
        AvailableLead::query()->insert($data);
    }

    /**
     * @inheritDoc
     */
    public function saveChunk(array $chunk, ?string $period = null): void
    {
        if($period !== null) {
            foreach ($chunk as &$data) {
                $data[AvailableLead::FIELD_UTC_DATE]   = $period;
                $data[AvailableLead::FIELD_CREATED_AT] = $data[AvailableLead::FIELD_UPDATED_AT] = Carbon::now();
            }
        }

        $this->save($chunk);
    }
}
