<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Request as RequestModel;
use App\Models\Equipment;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        switch ($user->role_id) {
            case 1:
                return $this->masterDashboard($user);
            case 2:
                return $this->managerDashboard($user);
            case 3:
                return $this->supervisorDashboard($user);
            case 4:
                return $this->staffDashboard($user);
            case 5:
                return $this->userDashboard($user);
            default:
                abort(403, 'Unauthorized role.');
        }
    }

    protected function masterDashboard($user)
    {
        $totalIncidents = Incident::count();
        $totalRequests = RequestModel::count();
        $totalEquipments = Equipment::count();
        $totalMaintenances = Maintenance::count();

        $incidents = Incident::where('status', 'waiting')->latest()->take(4)->get();
        $maintenances = Maintenance::where('status', 'maintenance')->latest()->take(4)->get();

        return view('dashboard.master', compact(
            'totalIncidents',
            'totalRequests',
            'totalEquipments',
            'totalMaintenances',
            'incidents',
            'maintenances'
        ));
    }

    protected function managerDashboard($user)
    {
        $totalIncidents = Incident::count();
        $totalRequests = RequestModel::count();
        $totalEquipments = Equipment::count();
        $totalMaintenances = Maintenance::count();

        $incidents = Incident::latest()->take(10)->get();
        $maintenances = Maintenance::latest()->take(10)->get();

        return view('dashboard.manager', compact(
            'totalIncidents',
            'totalRequests',
            'totalEquipments',
            'totalMaintenances',
            'incidents',
            'maintenances'
        ));
    }

    protected function supervisorDashboard($user)
    {
        $totalIncidents = Incident::where('department_to', $user->department)->count();
        $totalRequests = RequestModel::where('department_to', $user->department)->count();
        $totalEquipments = Equipment::count();
        $totalMaintenances = Maintenance::whereHas('equipment', function ($query) use ($user) {
            $query->where('location', $user->location);
        })->count();

        $incidents = Incident::where('department_to', $user->department)->latest()->take(10)->get();
        $maintenances = Maintenance::whereHas('equipment', function ($q) use ($user) {
            $q->where('location', $user->location);
        })->latest()->take(10)->get();

        return view('dashboard.supervisor', compact(
            'totalIncidents',
            'totalRequests',
            'totalEquipments',
            'totalMaintenances',
            'incidents',
            'maintenances'
        ));
    }

    protected function staffDashboard($user)
    {
        $totalIncidents = Incident::where('department_to', $user->department)->count();
        $totalRequests = RequestModel::where('department_to', $user->department)->count();
        $totalEquipments = Equipment::count();
        $totalMaintenances = Maintenance::where('picstaff', $user->id)->count();

        $incidents = Incident::where('department_to', $user->department)->latest()->take(10)->get();
        $maintenances = Maintenance::where('picstaff', $user->id)->latest()->take(10)->get();

        return view('dashboard.staff', compact(
            'totalIncidents',
            'totalRequests',
            'totalEquipments',
            'totalMaintenances',
            'incidents',
            'maintenances'
        ));
    }

    protected function userDashboard($user)
    {
        $totalIncidents = Incident::where('location', $user->store_location)->count();
        $totalRequests = RequestModel::where('location', $user->store_location)->count();
        $totalEquipments = Equipment::where('location', $user->store_location)->count();

        $incidents = Incident::where('location', $user->store_location)->where('status', 'waiting')
            ->latest()
            ->take(4)
            ->get();
        $requestsModel = RequestModel::where('location', $user->store_location)->where('status', 'waiting')
            ->latest()
            ->take(4)
            ->get();

        return view('dashboard.user', compact(
            'totalIncidents',
            'totalRequests',
            'totalEquipments',
            'incidents',
            'requestsModel'
        ));
    }
}
