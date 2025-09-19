<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Http\Requests\SubscriptionPlanRequest;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionPlan::query();
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('price', 'like', "%{$search}%")
                  ->orWhere('duration', 'like', "%{$search}%")
                  ->orWhere('interval', 'like', "%{$search}%");

                if (strtolower($search) === 'active') {
                    $q->orWhere('is_active', true);
                } elseif (strtolower($search) === 'inactive') {
                    $q->orWhere('is_active', false);
                }
            });
        }

        $perPage = $request->perPage ?? 10;

        return Inertia::render('SubscriptionPlans/Index', [
            'plans' => $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString(),
            'filters' => [
                'search' => $request->search,
                'perPage' => $perPage,
            ],
        ]);
    }

    public function store(SubscriptionPlanRequest $request)
    {
        SubscriptionPlan::create($request->validated());
        return redirect()->back()->with('success', 'Subscription Plan created successfully.');
    }

    public function update(SubscriptionPlanRequest $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update($request->validated());
        return redirect()->back()->with('success', 'Subscription Plan updated successfully.');
    }

    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->delete();

        return redirect()->back();
    }

    public function updateStatus(Request $request, SubscriptionPlan $plan)
    {
        $plan->update([
            'is_active' => $request->boolean('is_active')
        ]);
        return redirect()->back()->with('success', 'Status changed successfully.');
    }
}
