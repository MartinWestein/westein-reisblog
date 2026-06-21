@props(['status'])

@php
    $config = match ($status) {
        \App\Models\Subscriber::STATUS_PENDING => ['class' => 'text-bg-warning', 'label' => __('Wacht op bevestiging')],
        \App\Models\Subscriber::STATUS_ACTIVE => ['class' => 'text-bg-success', 'label' => __('Actief')],
        \App\Models\Subscriber::STATUS_UNSUBSCRIBED => ['class' => 'text-bg-secondary', 'label' => __('Uitgeschreven')],
        default => ['class' => 'text-bg-light', 'label' => $status],
    };
@endphp

<span class="badge {{ $config['class'] }}">{{ $config['label'] }}</span>
