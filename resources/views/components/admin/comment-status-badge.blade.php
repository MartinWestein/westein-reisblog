@props(['status'])
@php
    $map = [
        'pending'  => ['label' => 'Te modereren', 'class' => 'text-bg-warning'],
        'approved' => ['label' => 'Goedgekeurd',  'class' => 'text-bg-success'],
        'rejected' => ['label' => 'Afgekeurd',    'class' => 'text-bg-secondary'],
        'spam'     => ['label' => 'Spam',         'class' => 'text-bg-danger'],
    ];
    $config = $map[$status] ?? $map['pending'];
@endphp
<span class="badge {{ $config['class'] }}">{{ $config['label'] }}</span>
