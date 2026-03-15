@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-3xl p-10 shadow-sm border border-gray-100">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Pengguna Aplikasi</h2>
        <p class="text-gray-500">Melihat daftar email yang terdaftar dan jumlah Jar yang mereka miliki.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-xs uppercase text-gray-400 border-b border-gray-50">
                    <th class="py-4 font-medium">Nama Pengguna</th>
                    <th class="py-4 font-medium">Email</th>
                    <th class="py-4 font-medium">Total Jar</th>
                    <th class="py-4 font-medium">Bergabung</th>
                    <th class="py-4 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $user)
                <tr class="text-sm text-gray-600 hover:bg-gray-50 transition">
                    <td class="py-4 font-semibold text-gray-800">{{ $user->name }}</td>
                    <td class="py-4">{{ $user->email }}</td>
                    <td class="py-4 text-center">
                        <span class="bg-amber-50 text-amber-700 px-3 py-1 rounded-lg font-bold">
                            {{ $user->licenses_count }}
                        </span>
                    </td>
                    <td class="py-4">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="py-4">
                        <a href="{{ route('admin.users.show', $user->id) }}" class="text-emerald-islamic hover:underline font-medium">Detail Koleksi</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $users->links() }}
    </div>
</div>
@endsection
