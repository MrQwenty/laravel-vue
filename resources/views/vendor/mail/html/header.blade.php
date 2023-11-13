<tr>
<td class="header">
<a href="{{ $url }}" class="flex w-full" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://www.shock-wave.it/wp-content/uploads/2022/06/logo_shock_wave_bianco.png" class="logo" alt="Laravel Logo">
@else
<img src="{{\App\Helpers\MailHelper::getLogo()}}" class="logo" alt="Laravel Logo">
{{--{{ $slot }}--}}
@endif
</a>
</td>
</tr>
