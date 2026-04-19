<x-layouts.admin>

    <x-slot name="title">
        Entry/Exit Monitoring
    </x-slot>
    <x-slot name="pageName">
        Student Entry and Exit Monitoring
    </x-slot>

    <x-slot name="subtitle">
        School entry/exit gate scans.
    </x-slot>

    <div class="row g-3 mb-3 mx-2">
        
        <x-card title="entry" value="0" icon="fa-solid fa-right-to-bracket" variants="success" />
        <x-card title="exit" value="0" icon="fa-solid fa-door-open" variants="primary" />
        <x-card title="late" value="0" icon="fa-solid fa-hourglass-half" variants="warning" />
        <x-card title="early out" value="0" icon="fa-solid fa-right-from-bracket" variants="danger" />

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
             <td> </td>
            <td> </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </x-slot>
    </x-ui.table>


</x-layouts.admin>