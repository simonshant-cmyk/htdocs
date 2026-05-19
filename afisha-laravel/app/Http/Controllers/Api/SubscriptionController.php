<?php

namespace App\Http\Controllers\Api;

use App\Models\OrgSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends ApiController
{
    public function mySubscriptions(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof \App\Models\User)) {
            return $this->error('Нет доступа', 403);
        }

        $orgIds = OrgSubscription::where('user_id', $user->user_id)->pluck('organization_id');
        $orgs = \App\Models\Organization::whereIn('organization_id', $orgIds)
            ->orderBy('full_name')
            ->get()
            ->map(fn($org) => [
                'organization_id' => $org->organization_id,
                'full_name'       => $org->full_name,
                'image'           => $org->image,
                'address'         => $org->address,
            ]);

        return $this->success($orgs);
    }

    public function subscribe(Request $request, int $orgId): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof \App\Models\User)) {
            return $this->error('Только пользователи могут подписываться', 403);
        }

        OrgSubscription::firstOrCreate([
            'user_id'         => $user->user_id,
            'organization_id' => $orgId,
        ]);

        return $this->success(['subscribed' => true]);
    }

    public function unsubscribe(Request $request, int $orgId): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof \App\Models\User)) {
            return $this->error('Нет доступа', 403);
        }

        OrgSubscription::where('user_id', $user->user_id)
            ->where('organization_id', $orgId)
            ->delete();

        return $this->success(['subscribed' => false]);
    }

    public function status(Request $request, int $orgId): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof \App\Models\User)) {
            return $this->success(['subscribed' => false, 'count' => $this->count($orgId)]);
        }

        $subscribed = OrgSubscription::where('user_id', $user->user_id)
            ->where('organization_id', $orgId)
            ->exists();

        return $this->success(['subscribed' => $subscribed, 'count' => $this->count($orgId)]);
    }

    private function count(int $orgId): int
    {
        return OrgSubscription::where('organization_id', $orgId)->count();
    }
}
