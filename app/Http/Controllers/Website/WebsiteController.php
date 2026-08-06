<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;
use App\Models\PoojaPacket;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WebsiteController extends Controller
{
    public function home(): View
    {
        return view('website.home', [
            'featuredFlowers' => $this->catalogItems(FlowerProduct::class, 8),
            'featuredPackets' => $this->catalogItems(PoojaPacket::class, 4),
            'services' => $this->services(),
            'testimonials' => $this->testimonials(),
            'faqs' => $this->faqs(),
        ]);
    }

    public function about(): View { return view('website.about'); }

    public function flowers(Request $request): View
    {
        return view('website.flowers', [
            'flowers' => $this->catalogPage(FlowerProduct::class, $request, ['name', 'flower_name', 'title', 'description', 'category']),
        ]);
    }

    public function poojaPackets(Request $request): View
    {
        return view('website.pooja-packets', [
            'packets' => $this->catalogPage(PoojaPacket::class, $request, ['packet_name', 'name', 'title', 'description', 'package_type']),
        ]);
    }

    public function subscriptions(): View { return view('website.subscriptions'); }
    public function events(): View { return view('website.events'); }
    public function gallery(): View { return view('website.gallery'); }
    public function contact(): View { return view('website.contact'); }
    public function privacy(): View { return view('website.privacy'); }
    public function terms(): View { return view('website.terms'); }

    private function catalogItems(string $modelClass, int $limit): Collection
    {
        try {
            if (!class_exists($modelClass)) return collect();
            $model = new $modelClass();
            $query = $modelClass::query();
            $this->applyActiveFilter($query, $model->getTable());
            return $query->orderByDesc($model->getQualifiedKeyName())->limit($limit)->get();
        } catch (Throwable $exception) {
            report($exception);
            return collect();
        }
    }

    private function catalogPage(string $modelClass, Request $request, array $searchColumns): LengthAwarePaginator|Collection
    {
        try {
            if (!class_exists($modelClass)) return collect();
            $model = new $modelClass();
            $table = $model->getTable();
            $query = $modelClass::query();
            $this->applyActiveFilter($query, $table);
            $search = trim((string) $request->query('search', ''));

            if ($search !== '') {
                $available = collect($searchColumns)->filter(fn (string $column) => Schema::hasColumn($table, $column))->values();
                if ($available->isNotEmpty()) {
                    $query->where(function (Builder $builder) use ($available, $search): void {
                        foreach ($available as $index => $column) {
                            $builder->{$index === 0 ? 'where' : 'orWhere'}($column, 'like', '%' . $search . '%');
                        }
                    });
                }
            }

            return $query->orderByDesc($model->getQualifiedKeyName())->paginate(12)->withQueryString();
        } catch (Throwable $exception) {
            report($exception);
            return collect();
        }
    }

    private function applyActiveFilter(Builder $query, string $table): void
    {
        if (Schema::hasColumn($table, 'status')) {
            $query->whereIn('status', ['Active', 'active', 'ACTIVE', 1, '1']);
        } elseif (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }
    }

    private function services(): array
    {
        return [
            ['icon' => '🌸', 'title' => 'Fresh Flowers', 'description' => 'Handpicked flowers for worship, gifting and everyday freshness.', 'route' => 'website.flowers'],
            ['icon' => '🪔', 'title' => 'Pooja Packets', 'description' => 'Convenient flower and pooja essentials prepared for your daily rituals.', 'route' => 'website.pooja-packets'],
            ['icon' => '📅', 'title' => 'Subscriptions', 'description' => 'Daily, weekly and monthly delivery plans with flexible scheduling.', 'route' => 'website.subscriptions'],
            ['icon' => '✨', 'title' => 'Event Decoration', 'description' => 'Elegant floral styling for weddings, birthdays, pujas and corporate events.', 'route' => 'website.events'],
        ];
    }

    private function testimonials(): array
    {
        return [
            ['name' => 'Priyanka S.', 'role' => 'Subscription customer', 'quote' => 'The flowers arrive fresh and on time. My morning pooja is now much easier.'],
            ['name' => 'Rakesh M.', 'role' => 'Event customer', 'quote' => 'Fulawala made our family function beautiful with an elegant floral theme.'],
            ['name' => 'Ananya P.', 'role' => 'Pooja packet customer', 'quote' => 'The packet was neatly prepared and had everything needed for the occasion.'],
        ];
    }

    private function faqs(): array
    {
        return [
            ['question' => 'How do I place an order?', 'answer' => 'Browse flowers or pooja packets, then contact Fulawala or use the mobile app to complete your order.'],
            ['question' => 'Can I schedule daily flower delivery?', 'answer' => 'Yes. Daily, weekly and monthly plans are available according to your requirement.'],
            ['question' => 'Do you provide event decoration?', 'answer' => 'Yes. We provide customised floral decoration for weddings, birthdays, pujas, inaugurations and corporate events.'],
            ['question' => 'Which areas do you deliver to?', 'answer' => 'Service areas depend on the selected location. Contact our team to confirm availability for your address.'],
        ];
    }
}
