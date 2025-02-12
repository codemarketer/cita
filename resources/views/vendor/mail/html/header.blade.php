@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://clinicanyr.com/wp-content/uploads/2023/12/logo-clinica-nyr.png" style="height: auto; width: 300px;" alt="Clínica NYR Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
