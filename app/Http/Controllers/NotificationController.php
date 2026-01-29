<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $logs = \App\Models\NotificationLog::with('user')
            ->orderBy('sent_at', 'desc')
            ->paginate(15);
            
        return view('admin.notifications.index', compact('logs'));
    }

    public function test()
    {
        $users = \App\Models\User::orderBy('name')->get();
        return view('admin.notifications.test', compact('users'));
    }

    public function sendTest(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'channels' => 'required|array',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $manager = new \App\Services\NotificationManager();

        $results = $manager->send(
            user: $user,
            type: 'manual_test', // Tipo especial para que no filtre duplicados
            channels: $request->channels,
            appointment: null,
            data: [
                'title' => $request->title,
                'body'  => $request->body
            ]
        );

        return back()->with('ok', "Prueba enviada. Email: " . ($results['email'] ?? 'N/A') . " | Push: " . ($results['push'] ?? 'N/A') . " | WhatsApp: " . ($results['whatsapp'] ?? 'N/A'));
    }
    public function searchUsers(\Illuminate\Http\Request $request)
    {
        $q = trim($request->get('q'));
        
        $query = \App\Models\User::query()
            ->select('id', 'name', 'email') // role_id likely doesn't exist on users table
            ->with(['roles' => function($q) {
                $q->select('name', 'label');
            }])
            ->limit(50);
            
        if ($q) {
            $query->where(function($qq) use ($q) {
                $qq->where('name', 'LIKE', "%$q%")
                   ->orWhere('email', 'LIKE', "%$q%");
            });
        }
        
        $users = $query->orderBy('name')->get();
        
        // Format for frontend
        $data = $users->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->roles->first() ? ['name' => $u->roles->first()->name] : null
            ];
        });
        
        return response()->json($data);
    }
}
