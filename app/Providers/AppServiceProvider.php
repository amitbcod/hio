<?php

namespace App\Providers;

use App\Models\BookingWidget;
use App\Models\Operator;
use App\Models\OperatorProfile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer('frontend.*', function ($view) {
            $operatorToken = (string) request()->query('operator_token', session('operator_token', ''));
            $operatorProfile = null;

            if ($operatorToken !== '') {
                $widget = BookingWidget::where('widget_token', $operatorToken)
                    ->where('is_active', true)
                    ->first();

                if ($widget) {
                    $operator = Operator::find($widget->operator_id);
                    if ($operator && $operator->business_id) {
                        $operatorProfile = OperatorProfile::where(function ($query) use ($operator) {
                            $query->where('business_id', $operator->business_id);
                            if (!empty($operator->operator_id)) {
                                $query->orWhere('operator_id', $operator->operator_id);
                            }
                        })->first();
                    }
                }
            }

            $view->with('operatorToken', $operatorToken);
            $view->with('operatorProfile', $operatorProfile);
        });
    }
}
