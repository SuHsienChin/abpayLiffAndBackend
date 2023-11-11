<!-- resources/views/admin/users/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>使用者列表</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">新增使用者</a>
        <table class="table">
            <thead>
                <tr>
                    <th>名稱</th>
                    <th>Email</th>
                    <th>角色</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">編輯</a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('確定刪除？')">刪除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
