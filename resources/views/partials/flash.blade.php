@if(session('success'))
    <div class="notice notice-ok">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="notice notice-err">{{ session('error') }}</div>
@endif
