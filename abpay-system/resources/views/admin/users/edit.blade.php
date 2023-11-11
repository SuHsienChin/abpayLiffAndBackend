<!-- resources/views/admin/users/edit.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>編輯使用者</h1>
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">名稱：</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email：</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
            </div>
            <div class="form-group">
                <label for="role">角色：</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="admin" @if ($user->role === 'admin') selected @endif>管理員</option>
                    <option value="customer" @if ($user->role === 'customer') selected @endif>顧客</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">更新使用者</button>
        </form>
    </div>
@endsection
