<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
โรงเรียนศิริราษฎร์สามัคคี
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} โรงเรียนศิริราษฎร์สามัคคี. สงวนลิขสิทธิ์ทั้งหมด.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
