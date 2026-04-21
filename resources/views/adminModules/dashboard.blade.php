<x-layouts.admin>
    <x-slot name="title">
        Dashboard
    </x-slot>

    <x-slot name="pageName">
        Dashboard
    </x-slot>

    <x-slot name="subtitle">
        Monitor and view recent entry exit scans
    </x-slot>

    {{-- contents inside eontainer fluid --}}
    <div class="row g-3 mb-3 justify-content-center">
        <x-card title="entry scans" value="0" icon="fa-solid fa-door-open" variants="success" />
        <x-card title="exit scans" value="0" icon="fa-solid fa-door-open" variants="primary" />
        <x-card title="flagged scans" value="0" icon="fa-solid fa-door-open" variants="warning" />

    </div>



    <x-ui.table>
        <x-slot name="thead">
            <th> Date</th>
            <th> name</th>
            <th>grade</th>
            <th>section</th>
            <th>gate time</th>
            <th>type</th>
            <th>session</th>
            <th>status</th>
        </x-slot>


        <x-slot name="tbody">

            <tr>
                <td> </td>
                <td> </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

        </x-slot>
    </x-ui.table>



</x-layouts.admin>