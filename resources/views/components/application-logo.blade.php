@props(['class' => 'block h-9 w-auto'])

<img src="{{ asset('images/logo.png') }}"
     alt="Chitwan Computer"
     {{ $attributes->merge(['class' => 'rounded-md object-contain '.$class]) }} />
