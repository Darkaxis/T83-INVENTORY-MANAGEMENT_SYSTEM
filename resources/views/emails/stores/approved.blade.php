<!-- filepath: d:\WST\inventory-management-system\resources\views\emails\stores\approved.blade.php -->
@component('mail::message')
# Good news!

Your store "{{ $storeName }}" has been approved and is now ready to use.

@if ($hasCustomUrl)
You can access your store at: {{ $storeUrl }}
@else
You can access your store through our platform after login.
@endif

@if ($loginDetails)
## Your Login Details

**Email:** {{ $loginDetails['email'] }}  
**Password:** {{ $loginDetails['password'] }}

Please change your password after your first login for security reasons.
@endif

@component('mail::button', ['url' => $storeUrl])
Visit Your Store
@endcomponent

Thank you for choosing our platform!

Regards,<br>
{{ config('app.name') }}
@endcomponent