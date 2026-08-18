<x-layouts.admin>
    <x-slot name="title">
        User
    </x-slot>
    <x-slot name="pageName">
        User and Role Management
    </x-slot>

    <x-slot name="subtitle">Manage user accounts, roles, and access permissions.</x-slot>

    <div class="main-content mx-3">
        <x-ui.table>
            <thead class="text-uppercase small">
                <tr>
                    <th style="width: 15%">
                        <span class="fas fa-id-card me-1"></span> User id
                    </th>
                    <th>
                        <span class="fas fa-user me-1"></span> Name
                    </th>
                    <th>
                        <span class="fas fa-user-tag me-1"></span> Role
                    </th>
                    <th>
                        <span class="fas fa-envelope me-1"></span> Email
                    </th>
                    <th>
                        <span class="fas fa-circle me-1"></span> Status
                    </th>
                    <th style="width: 15%">
                        <span class="fas fa-sliders-h me-1"></span> Actions
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
            </tbody>
        </x-ui.table>
    </div>
</x-layouts.admin>