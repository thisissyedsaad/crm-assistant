<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpWhitelist;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IpWhitelistController extends Controller
{
    public function __construct()
    {
        // Only super-admin can access this controller
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'super-admin') {
                abort(403, 'Access denied. Super admin privileges required.');
            }
            return $next($request);
        });
    }

    /**
     * Display IP whitelist
     */
    public function index(Request $request)
    {
        $query = IpWhitelist::with('creator');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('label', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $ipWhitelists = $query->latest()->paginate(15);

        return view('admin.ip-whitelist.index', compact('ipWhitelists'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.ip-whitelist.create');
    }

    /**
     * Store new IP whitelist entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'ip_address' => [
                'required',
                'ip',
                Rule::unique('ip_whitelists', 'ip_address')
            ],
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        IpWhitelist::create([
            'ip_address' => $request->ip_address,
            'label' => $request->label,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'created_by' => auth()->id()
        ]);

        return redirect()->route('admin.ip-whitelist.index')
                        ->with('success', 'IP address added to whitelist successfully!');
    }

    /**
     * Show edit form
     */
    public function edit(IpWhitelist $ipWhitelist)
    {
        return view('admin.ip-whitelist.edit', compact('ipWhitelist'));
    }

    /**
     * Update IP whitelist entry
     */
    public function update(Request $request, IpWhitelist $ipWhitelist)
    {
        $request->validate([
            'ip_address' => [
                'required',
                'ip',
                Rule::unique('ip_whitelists', 'ip_address')->ignore($ipWhitelist->id)
            ],
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        $ipWhitelist->update([
            'ip_address' => $request->ip_address,
            'label' => $request->label,
            'description' => $request->description,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.ip-whitelist.index')
                        ->with('success', 'IP whitelist updated successfully!');
    }

    /**
     * Delete IP whitelist entry
     */
    public function destroy(IpWhitelist $ipWhitelist)
    {
        $ipWhitelist->delete();

        return redirect()->route('admin.ip-whitelist.index')
                        ->with('success', 'IP address removed from whitelist successfully!');
    }

    /**
     * Toggle IP status (active/inactive)
     */
    public function toggleStatus(IpWhitelist $ipWhitelist)
    {
        $ipWhitelist->update([
            'is_active' => !$ipWhitelist->is_active
        ]);

        $status = $ipWhitelist->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "IP address {$status} successfully!",
            'is_active' => $ipWhitelist->is_active
        ]);
    }

    /**
     * Bulk status update
     */
    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ip_whitelists,id',
            'status' => 'required|boolean'
        ]);

        IpWhitelist::whereIn('id', $request->ids)
                  ->update(['is_active' => $request->status]);

        $action = $request->status ? 'activated' : 'deactivated';
        $count = count($request->ids);

        return response()->json([
            'success' => true,
            'message' => "{$count} IP addresses {$action} successfully!"
        ]);
    }

    /**
     * Get current user's IP
     */
    public function getCurrentIp(Request $request)
    {
        return response()->json([
            'ip' => $request->ip()
        ]);
    }
}