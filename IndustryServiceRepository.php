<?php

namespace App\Repositories\Odin;

use App\Contracts\Repositories\Odin\IndustryServiceRepositoryContract;
use App\Models\Odin\Industry;
use App\Models\Odin\IndustryService;
use Illuminate\Support\Collection;

class IndustryServiceRepository implements IndustryServiceRepositoryContract
{
    /**
     * @inheritDoc
     */
    public function getIndustryServices(int $industry): Collection
    {
        return IndustryService::query()->where(IndustryService::FIELD_INDUSTRY_ID, $industry)->latest()->get();
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreateIndustryService(int $industry, string $name, string $slug, int $showOnWebsite, ?int $id = null): bool
    {
        return IndustryService::query()->updateOrCreate(
                [
                    IndustryService::FIELD_ID => $id
                ],
                [
                    IndustryService::FIELD_INDUSTRY_ID      => $industry,
                    IndustryService::FIELD_NAME             => $name,
                    IndustryService::FIELD_SLUG             => $slug,
                    IndustryService::FIELD_SHOW_ON_WEBSITE  => $showOnWebsite === 1 ?? 0
                ]
            ) !== null;
    }

    /**
     * @inheritDoc
     */
    public function deleteIndustryService(int $id): bool
    {
        return IndustryService::query()->where(IndustryService::FIELD_ID, $id)->delete();
    }

    /**
     * @inheritDoc
     */
    public function getAllServices(): Collection
    {
        return IndustryService::query()->latest()->get();
    }

    /**
     * @param string $slug
     * @return IndustryService|null
     */
    public function getIndustryServiceBySlug(string $slug): ?IndustryService
    {
        /** @var IndustryService $service */
        $service = IndustryService::query()->where(IndustryService::FIELD_SLUG, $slug)->first();
        return $service;
    }

    /**
     * @param string $industryName
     * @param string $serviceName
     * @return IndustryService|null
     */
    public function findIndustryServiceByNames(string $industryName, string $serviceName): ?IndustryService
    {
        $industryId = Industry::query()
            ->where(Industry::FIELD_NAME, $industryName)
            ->first()
            ?->{Industry::FIELD_ID};
        /** @var IndustryService $service */
        $service = $industryId
            ? IndustryService::query()
                ->where(IndustryService::FIELD_INDUSTRY_ID, $industryId)
                ->where(IndustryService::FIELD_NAME, $serviceName)
                ->first()
            : null;
        return $service;
    }
}
