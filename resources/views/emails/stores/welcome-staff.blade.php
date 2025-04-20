<!-- filepath: d:\WST\inventory-management-system\resources\views\emails\welcome-staff.blade.php -->
@component('mail::message')
# Welcome to {{ $store->name }}!

You have been added as a staff member to **{{ $store->name }}** inventory management system.

## Your Login Details:
- **Email**: {{ request()->email }}
- **Temporary Password**: {{ $password }}

Please login and change your password as soon as possible.

@component('mail::button', ['url' => $loginUrl])
Login Now
@endcomponent

**Important Security Notice:**
This temporary password should be changed immediately after your first login for security purposes.

Thank you,<br>
The {{ $store->name }} Team
@endcomponent