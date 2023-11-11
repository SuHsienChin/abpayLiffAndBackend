<!-- resources/views/admin/users/create.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>新增使用者</h1>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">名稱：</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email：</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">密碼：</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">確認密碼：</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="role">角色：</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="admin">管理員</option>
                    <option value="customer">顧客</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">新增使用者</button>
        </form>
    </div>
@endsection
