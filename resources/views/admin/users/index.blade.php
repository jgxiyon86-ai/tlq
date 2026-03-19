@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-8 rounded-[2rem] shadow-sm border border-emerald-50 mb-8 overflow-hidden relative">
        <div class="relative z-10">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen <span class="text-emerald-600">Users & RBAC</span></h2>
            <p class="text-gray-500 text-sm mt-1">Atur hak akses admin, monitor aktivitas, dan kelola akun jemaah.</p>
        </div>
        
        <form action="{{ route('admin.users.index') }}" method="GET" class="relative group w-full sm:w-72 mt-6 md:mt-0">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari Nama / Email..." 
                class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Identitas</th>
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Peran (Role)</th>
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Hak Akses</th>
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $u)
                <tr class="hover:bg-gray-50/30 transition group">
                    <td class="px-8 py-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-black shadow-inner">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-black text-gray-800 text-sm tracking-tight">{{ $u->name }}</p>
                                <p class="text-xs text-gray-400">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        @if($u->role === 'super_admin')
                            <span class="px-4 py-1.5 bg-rose-50 text-rose-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100 shadow-sm">Super Admin</span>
                        @elseif($u->role === 'admin')
                            <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100 shadow-sm">Admin Tim</span>
                        @else
                            <span class="px-4 py-1.5 bg-gray-100 text-gray-500 rounded-full text-[10px] font-black uppercase tracking-widest">Jemaah</span>
                        @endif
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-wrap justify-center gap-2">
                            @if($u->role === 'super_admin')
                                <span class="bg-gray-800 text-white text-[9px] px-2 py-0.5 rounded-lg opacity-30">Akses Penuh</span>
                            @else
                                @if($u->can_manage_licenses) <span class="bg-amber-100 text-amber-700 text-[9px] font-black px-2 py-0.5 rounded-lg border border-amber-200">Licenses</span> @endif
                                @if($u->can_manage_contents) <span class="bg-blue-100 text-blue-700 text-[9px] font-black px-2 py-0.5 rounded-lg border border-blue-200">Contents</span> @endif
                                @if($u->can_manage_guides) <span class="bg-purple-100 text-purple-700 text-[9px] font-black px-2 py-0.5 rounded-lg border border-purple-200">Guides</span> @endif
                                @if(!$u->can_manage_licenses && !$u->can_manage_contents && !$u->can_manage_guides)
                                    <span class="text-[9px] text-gray-300 italic">No special access</span>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end items-center space-x-2">
                            <button onclick="openRoleModal({{ json_encode($u) }})" class="p-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition shadow-sm" title="Atur Hak Akses">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                            <button onclick="openPasswordModal({{ $u->id }}, '{{ $u->name }}')" class="p-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 transition shadow-sm" title="Ubah Password">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-8 py-6 bg-gray-50/30">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal Atur Role (RBAC) -->
<div id="roleModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeRoleModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100 p-8">
            <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-2">Atur <span class="text-emerald-600">Hak Akses</span></h3>
            <p id="roleUserEmail" class="text-xs text-gray-400 font-bold mb-8 uppercase tracking-widest">USER: ...</p>

            <form id="roleForm" action="" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Tingkatan Akun</label>
                    <div class="grid grid-cols-1 gap-3">
                        <label class="flex items-center p-4 bg-gray-50 rounded-2xl cursor-pointer border-2 border-transparent peer-checked:border-emerald-500 hover:bg-emerald-50 transition group">
                            <input type="radio" name="role" value="super_admin" class="peer hidden" id="r_super">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex-shrink-0 group-hover:border-emerald-500 flex items-center justify-center">
                                <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full hidden"></div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-black text-gray-800">Super Admin (Ustadz Besar)</p>
                                <p class="text-[10px] text-gray-400">Akses penuh ke semua fitur data dan user.</p>
                            </div>
                        </label>
                        <label class="flex items-center p-4 bg-gray-50 rounded-2xl cursor-pointer border-2 border-transparent hover:bg-emerald-50 transition group">
                            <input type="radio" name="role" value="admin" class="peer hidden" id="r_admin">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex-shrink-0 flex items-center justify-center">
                                <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full hidden"></div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-black text-gray-800">Admin Tim (Petugas)</p>
                                <p class="text-[10px] text-gray-400">Bisa masuk dashboard, hak khusus klik di bawah.</p>
                            </div>
                        </label>
                        <label class="flex items-center p-4 bg-gray-50 rounded-2xl cursor-pointer border-2 border-transparent hover:bg-emerald-50 transition group">
                            <input type="radio" name="role" value="user" class="peer hidden" id="r_user">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex-shrink-0 flex items-center justify-center">
                                <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full hidden"></div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-black text-gray-800">Jemaah (User Biasa)</p>
                                <p class="text-[10px] text-gray-400">Hanya bisa akses lewat aplikasi Android.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="permissionsPanel" class="pt-2 border-t border-gray-100">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Hak Akses Khusus (Admin Tim)</label>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-amber-50 group transition">
                            <div class="flex items-center mr-2">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mr-3 font-bold">🔑</div>
                                <span class="text-xs font-bold text-gray-700">Manajemen Licenses</span>
                            </div>
                            <input type="checkbox" name="can_manage_licenses" value="1" class="w-5 h-5 text-emerald-600 border-gray-300 rounded-lg focus:ring-emerald-500">
                        </label>
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-blue-50 group transition">
                            <div class="flex items-center mr-2">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3 font-bold">📖</div>
                                <span class="text-xs font-bold text-gray-700">Manajemen Contents (Ayat)</span>
                            </div>
                            <input type="checkbox" name="can_manage_contents" value="1" class="w-5 h-5 text-emerald-600 border-gray-300 rounded-lg focus:ring-emerald-500">
                        </label>
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-purple-50 group transition">
                            <div class="flex items-center mr-2">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-3 font-bold">📌</div>
                                <span class="text-xs font-bold text-gray-700">Manajemen Guides (Panduan)</span>
                            </div>
                            <input type="checkbox" name="can_manage_guides" value="1" class="w-5 h-5 text-emerald-600 border-gray-300 rounded-lg focus:ring-emerald-500">
                        </label>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-emerald-600 text-white font-black py-4 rounded-3xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-100 uppercase text-[10px] tracking-[0.2em]">
                        Simpan Perubahan Role
                    </button>
                    <button type="button" onclick="closeRoleModal()" class="w-full mt-3 text-[10px] font-black text-gray-400 uppercase tracking-widest py-2 hover:text-rose-500 transition-colors">Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ganti Password -->
<div id="passwordModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closePasswordModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100 p-8">
            <h3 class="text-2xl font-black text-gray-900 tracking-tight mb-6 uppercase">Ubah <span class="text-amber-600">Password</span></h3>
            
            <form id="passwordForm" action="" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Password Baru (min 8 car)</label>
                    <input type="password" name="password" required class="w-full p-4 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-amber-500/20">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required class="w-full p-4 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-amber-500/20">
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full bg-gray-900 text-white font-black py-4 rounded-3xl hover:bg-emerald-600 transition-colors shadow-lg uppercase text-[10px] tracking-[0.2em]">
                        Update Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRoleModal(user) {
        document.getElementById('roleUserEmail').textContent = `USER: ${user.name} (${user.email})`;
        document.getElementById('roleForm').action = `/admin/users/${user.id}/promote`;
        
        // Check Role
        const role = user.role || 'user';
        const rad = document.querySelector(`input[name="role"][value="${role}"]`);
        if (rad) rad.checked = true;
        
        // Check permissions
        document.querySelector('input[name="can_manage_licenses"]').checked = user.can_manage_licenses == 1;
        document.querySelector('input[name="can_manage_contents"]').checked = user.can_manage_contents == 1;
        document.querySelector('input[name="can_manage_guides"]').checked = user.can_manage_guides == 1;

        document.getElementById('roleModal').classList.remove('hidden');
    }

    function closeRoleModal() { document.getElementById('roleModal').classList.add('hidden'); }

    function openPasswordModal(userId, userName) {
        document.getElementById('passwordForm').action = `/admin/users/${userId}/password`;
        document.getElementById('passwordModal').classList.remove('hidden');
    }

    function closePasswordModal() { document.getElementById('passwordModal').classList.add('hidden'); }
</script>

<style>
/* Styling radio peer effect */
input[name="role"]:checked + div { @apply border-emerald-500 bg-emerald-50; }
input[name="role"]:checked + div > div { @apply block; }
</style>
@endsection
