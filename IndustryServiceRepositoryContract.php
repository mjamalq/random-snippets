<?php

namespace App\Contracts\Repositories\Odin;

use App\Models\Odin\IndustryService;
use Illuminate\Support\Collection;

interface IndustryServiceRepositoryContract
{
    /**
     * @param int $industry
     * @return Collection<IndustryService>
     */
    public function getIndustryServices(int $industry): Collection;

    /**
     * @param int $industry
     * @param string $name
     * @param string $slug
     * @param int $showOnWebsite
     * @param int|null $id
     * @return bool
     */
    public function updateOrCreateIndustryService(int $industry, string $name, string $slug, int $showOnWebsite, ?int $id = null): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function deleteIndustryService(int $id): bool;

    /**
     * @return Collection<IndustryService>
     */
    public function getAllServices(): Collection;
}
