<?php

namespace Devpratyusa\UserDiscounts\Services;

use Devpratyusa\UserDiscounts\Events\DiscountApplied;
use Devpratyusa\UserDiscounts\Events\DiscountAssigned;
use Devpratyusa\UserDiscounts\Events\DiscountRevoked;
use Devpratyusa\UserDiscounts\Models\Discount;
use Devpratyusa\UserDiscounts\Models\DiscountAudit;
use Devpratyusa\UserDiscounts\Models\UserDiscount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiscountManager
{
    public function assign($user, Discount $discount)
    {
        $userDiscount = UserDiscount::updateOrCreate(
            ['user_id' => $user->id, 'discount_id' => $discount->id],
            ['revoked_at' => null]
        );

        event(new DiscountAssigned($user, $discount));

        return $userDiscount;
    }

    public function revoke($user, Discount $discount)
    {
        UserDiscount::where('user_id', $user->id)
            ->where('discount_id', $discount->id)
            ->update(['revoked_at' => now()]);

        event(new DiscountRevoked($user, $discount));
    }

    public function eligibleFor($user, Discount $discount): bool
    {
        if (! $discount->isActive()) {
            return false;
        }

        $pivot = UserDiscount::where('user_id', $user->id)
            ->where('discount_id', $discount->id)
            ->first();

        if (! $pivot || $pivot->isRevoked()) {
            return false;
        }

        if ($discount->max_usage_per_user && $pivot->usages >= $discount->max_usage_per_user) {
            return false;
        }

        return true;
    }

    public function apply($user, float $amount, ?string $applicationUid = null): float
    {
        $result = $this->applyWithDetails($user, $amount, $applicationUid);

        return $result['final_amount'];
    }

    public function applyWithDetails($user, float $amount, ?string $applicationUid = null): array
    {
        $applicationUid = $applicationUid ?: (string) Str::uuid();

        return DB::transaction(function () use ($user, $amount, $applicationUid) {
            // Idempotency check
            if (DiscountAudit::where('application_uid', $applicationUid)->exists()) {
                $existing = DiscountAudit::where('application_uid', $applicationUid)->first();

                return [
                    'final_amount' => $existing->discounted_amount,
                    'saved' => $existing->saved,
                    'audit' => $existing,
                ];
            }

            $eligible = $this->getEligibleDiscounts($user);

            $current = $amount;
            $totalPercentageApplied = 0;
            $applied = [];
            $skipped = [];

            $config = config('user-discounts');

            foreach ($eligible as $discount) {
                $pivot = $discount->pivot;

                if ($discount->type === 'percentage') {
                    if ($totalPercentageApplied + $discount->value > $config['max_percentage_cap']) {
                        $skipped[] = ['discount' => $discount, 'reason' => 'percentage_cap_exceeded'];

                        continue;
                    }
                }

                $saved = $discount->type === 'percentage'
                    ? $current * ($discount->value / 100)
                    : $discount->value;

                $saved = min($saved, $current); // don't go negative
                $current -= $saved;

                if ($discount->type === 'percentage') {
                    $totalPercentageApplied += $discount->value;
                }

                $applied[] = ['discount' => $discount, 'saved' => $saved];

                // Atomically increment usage with row lock
                DB::statement(
                    'UPDATE user_discounts SET usages = usages + 1 WHERE id = ? AND usages < ?',
                    [$pivot->id, $discount->max_usage_per_user ?? PHP_INT_MAX]
                );
            }

            $final = round($current, $config['precision'], $config['rounding_mode']);
            $savedTotal = $amount - $final;

            $audit = DiscountAudit::create([
                'user_id' => $user->id,
                'original_amount' => $amount,
                'discounted_amount' => $final,
                'saved' => $savedTotal,
                'application_uid' => $applicationUid,
                'applied_discounts' => $applied,
                'skipped_discounts' => $skipped,
            ]);

            event(new DiscountApplied($audit));

            return [
                'final_amount' => $final,
                'saved' => $savedTotal,
                'audit' => $audit,
            ];
        });
    }

    protected function getEligibleDiscounts($user)
    {
        $order = config('user-discounts.stacking_order') === 'priority'
            ? 'discounts.stacking_priority ASC'
            : 'discounts.created_at ASC';

        return UserDiscount::with('discount')
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->join('discounts', 'user_discounts.discount_id', '=', 'discounts.id')
            ->where('discounts.active', true)
            ->where(function ($q) {
                $q->whereNull('discounts.starts_at')->orWhere('discounts.starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('discounts.ends_at')->orWhere('discounts.ends_at', '>=', now());
            })
            ->whereRaw('(discounts.max_usage_per_user IS NULL OR user_discounts.usages < discounts.max_usage_per_user)')
            ->orderByRaw($order)
            ->select('user_discounts.*')
            ->get()
            ->filter(fn ($pivot) => $pivot->discount->isActive());
    }
}
