<x-ui.table>
    <thead>
        <tr>
            <th style="width: 15%">Student No. </th>
            <th style="width: 70%">Full name</th>
        
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>{{$notEnrolled->first()->student->first_name ?? 'null'}} </td>
            <td> </td>
            <td> </td>
        </tr>
    </tbody>
</x-ui.table>
