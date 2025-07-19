<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Request as RequestModel;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\Store;
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
        $totalUser = User::count();
        $totalOutlet = Store::count();

        $incidents = Incident::whereNotIn('status', ['completed'])->latest()->take(4)->get();
        $maintenances = Maintenance::whereNotIn('status', ['completed', 'cancelled'])->latest()->take(4)->get();
        $users = User::latest()->take(4)->get();
        $outlet = Store::latest()->take(4)->get();

        return view('dashboard.master', compact(
            'totalIncidents',
            'totalRequests',
            'totalEquipments',
            'totalMaintenances',
            'totalUser',
            'totalOutlet',
            'incidents',
            'maintenances'
        ));
    }

    protected function managerDashboard($user)
    {
        $totalIncidents = Incident::where('department_to', $user->department_id)->count();
        $totalRequests = RequestModel::where('department_to', $user->department_id)->count();
        $totalEquipments = Equipment::count();
        $totalMaintenances = Maintenance::whereHas('equipment.item', function ($query) use ($user) {
            $query->where('department_id', $user->department_id);
        })->count();

        $incidents = Incident::where('department_to', $user->department_id)->whereNotIn('status', ['completed'])->latest()->take(4)->get();
        $requests = RequestModel::where('department_to', $user->department_id)->whereNotIn('status', ['completed'])->latest()->take(4)->get();
        $maintenances = Maintenance::whereHas('equipment.item', function ($q) use ($user) {
            $q->where('department_id', $user->department_id);
        })->whereNotIn('status', ['completed'])->latest()->take(4)->get();
        return view('dashboard.manager', compact(
            'totalIncidents',
            'totalRequests',
            'totalEquipments',
            'totalMaintenances',
            'incidents',
            'requests',
            'maintenances'
        ));
    }

    protected function supervisorDashboard($user)
    {
        $totalIncidents = Incident::where('department_to', $user->department_id)->count();
        $totalRequests = RequestModel::where('department_to', $user->department_id)->count();
        $totalEquipments = Equipment::count();
        $totalMaintenances = Maintenance::whereHas('equipment.item', function ($query) use ($user) {
            $query->where('department_id', $user->department_id);
        })->count();

        $incidents = Incident::where('department_to', $user->department_id)->whereNotIn('status', ['completed'])->latest()->take(4)->get();
        $requests = RequestModel::where('department_to', $user->department_id)->whereNotIn('status', ['completed'])->latest()->take(4)->get();
        $maintenances = Maintenance::whereHas('equipment.item', function ($q) use ($user) {
            $q->where('department_id', $user->department_id);
        })->whereNotIn('status', ['completed'])->latest()->take(4)->get();

        return view('dashboard.supervisor', compact(
            'totalIncidents',
            'totalRequests',
            'totalEquipments',
            'totalMaintenances',
            'incidents',
            'maintenances',
            'requests',
        ));
    }

    protected function staffDashboard($user)
    {
        $totalIncidents = Incident::where('department_to', $user->department_id)->count();
        $totalRequests = RequestModel::where('department_to', $user->department_id)->count();
        $totalEquipments = Equipment::count();
        $totalMaintenances = Maintenance::whereHas('equipment.item', function ($query) use ($user) {
            $query->where('department_id', $user->department_id);
        })->count();

        $incidents = Incident::where('department_to', $user->department_id)->whereNotIn('status', ['completed'])->latest()->take(4)->get();
        $requests = RequestModel::where('department_to', $user->department_id)->whereNotIn('status', ['completed'])->latest()->take(4)->get();
        $maintenances = Maintenance::whereHas('equipment.item', function ($q) use ($user) {
            $q->where('department_id', $user->department_id);
        })->whereNotIn('status', ['completed'])->latest()->take(4)->get();

        return view('dashboard.staff', compact(
            'totalIncidents',
            'totalRequests',
            'totalEquipments',
            'totalMaintenances',
            'incidents',
            'maintenances',
            'requests'
        ));
    }

    protected function userDashboard($user)
    {
        $totalIncidents = Incident::where('location', $user->store_location)->count();
        $totalRequests = RequestModel::where('location', $user->store_location)->count();
        $totalEquipments = Equipment::where('location', $user->store_location)->count();

        $incidents = Incident::where('location', $user->store_location)->whereNotIn('status', ['completed'])
            ->latest()
            ->take(4)
            ->get();
        $requestsModel = RequestModel::where('location', $user->store_location)->whereNotIn('status', ['completed'])
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
