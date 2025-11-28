<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Settings;
use App\Models\Logo;
use Illuminate\Support\Facades\View;
use app;
use Session;
use Image;
use Illuminate\Database\Query\Builder;
use App\Facades\CroneFacade;
use App\Models\CompanyBranch;
use App\Models\SamraddhBank;

use Aws\S3\S3Client;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind('cronefacade', function () {
            return new CroneFacade();
        });

        $this->app->singleton('s3', function () {

            return new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key' => config('services.ses.key'),
                    'secret' => config('services.ses.secret'),
                ]
            ]);
        });

        Builder::macro('getCompanyRecords', function ($column, $value) {

            $getType = getType($value);

            $query = $this;
            $whereColumn = 'where' . (ucfirst($column));

            return $this->when($getType == 'array', function ($q) use ($value) {

                $q->whereJsonContains('company_id', $value);
            })->when($getType != 'array', function ($q) use ($value, $whereColumn) {
                $q->$whereColumn($value);
            });
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $data['set'] = Settings::first(['site_name', 'site_desc', 'asset', 'py_scheme', 'save', 'loan', 'merchant', 'gradient1', 'gradient2']);
        $data['logo'] = Logo::first(['image_link', 'image_link2']);
        $data['company'] = \App\Models\Companies::getCompany('1')->where('delete', '0')->Pluck('name', 'id');
        $data['allCompany'] = \App\Models\Companies::getCompany('')->Pluck('name', 'id');
        $data['AllCompany'] = \App\Models\Companies::withoutGlobalScopes()->Pluck('name', 'id');
        $data['statusCompany'] = \App\Models\Companies::whereStatus('1')->Pluck('name', 'id');
        $data['companyBranch'] = \App\Models\CompanyBranch::with([
            'branch' => function ($q) {
                $q->whereStatus('1');
            }
        ])->whereStatus('1')->get()->groupBy('company_id');
        $data['allcompanyBranch'] = \App\Models\CompanyBranch::with(['branch'])->whereStatus('1')->get()->groupBy('company_id');
        $data['branchCompany'] = \App\Models\CompanyBranch::with([
            'get_company' => function ($q) {
                $q->whereStatus('1');
            }
        ])->whereStatus('1')->get()->groupBy('branch_id');
        $data['companyBank'] = \App\Models\SamraddhBank::whereStatus('1')->get()->groupBy('company_id');
        $data['bank'] = \App\Models\SamraddhBank::has('company')->whereStatus('1')->PlucK('bank_name', 'id');
        $this->app->bind('data', function () use ($data) {
            return $data;
        });
        view::share($data);
    }
}
