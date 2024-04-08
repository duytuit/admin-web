<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(BOCustomersTableSeeder::class);
        // $this->call(BoUsersTableSeeder::class);
        // $this->call(BoUserGroupsTableSeeder::class);
        // $this->call(BoCategoriesTableSeeder::class);

        // $this->call(CitiesTableSeeder::class);
        // $this->call(DistrictsTableSeeder::class);

        // $this->call(PartnersTableSeeder::class);
        // $this->call(BranchesTableSeeder::class);
        // $this->call(UserPartnersTableSeeder::class);

        // $this->call(FeedbackTableSeeder::class);
        // $this->call(ExchangesTableSeeder::class);

        // $this->call(RolesTableSeeder::class);

        // $this->call(SettingsTableSeeder::class);

        // $this->call(CategoriesTableSeeder::class);

        // $this->call(ArticlesTableSeeder::class);
        // $this->call(EventsTableSeeder::class);
        // $this->call(VouchersTableSeeder::class);

        // $this->call(PostsTableSeeder::class);

        // $this->call(PostEmotionsTableSeeder::class);
        // $this->call(PostFollowsTableSeeder::class);
        // $this->call(PostPollsTableSeeder::class);
        // // $this->call(PostRegistersTableSeeder::class);
        // $this->call(PostSharesTableSeeder::class);
        // $this->call(PostVotesTableSeeder::class);

        // $this->call(CommentsTableSeeder::class);

        // $this->call(PostsResponseFieldSeeder::class);

        // $this->call(PostsImageFieldSeeder::class);
        // $this->call(FilterTableSeeder::class);
        $this->call(UserPublicTableSeeder::class);
        $this->call(AssetSeeder::class);
        $this->call(ServiceTableSeeder::class);
        $this->call(BDCHandbookSeeder::class);
        $this->call(BuildingSeeder::class);
        $this->call(TaskSeeder::class);
        $this->call(UserPermissionSeeder::class);
        $this->call(BdcPriceTypeTableSeeder::class);
        $this->call(BDCCompanySeeder::class);

    }
}