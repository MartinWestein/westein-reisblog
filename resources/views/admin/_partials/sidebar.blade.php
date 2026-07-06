<aside class="admin-sidebar" :class="{ 'is-open': mobileOpen }">
    <a href="{{ route('admin.home') }}" class="admin-sidebar__brand">
        <span class="admin-sidebar__brand-mark">W</span>
        <span class="admin-sidebar__brand-text">Westein Beheer</span>
    </a>
    <nav class="admin-sidebar__nav">
        <div class="admin-sidebar__section">
            <x-admin.nav-link route="admin.home" icon="bi-speedometer2" label="Dashboard" />
        </div>
        <div class="admin-sidebar__section">
            <div class="admin-sidebar__section-label">{{ __('Content') }}</div>
            <x-admin.nav-link route="admin.posts.index" icon="bi-journal-text" label="Posts" can="posts.viewAny" />
            <x-admin.nav-link route="admin.destinations.index" icon="bi-globe-europe-africa" label="Bestemmingen" can="content.manage" />
            <x-admin.nav-link route="admin.reisroutes.index" icon="bi-signpost-split" label="Routes" can="content.manage" />
            <x-admin.nav-link route="admin.pages.index" icon="bi-file-earmark-text" label="Pagina's" can="content.manage" />
            <x-admin.nav-link route="admin.family-members.index" icon="bi-people" label="Familie" can="content.manage" />
        </div>
        <div class="admin-sidebar__section">
            <div class="admin-sidebar__section-label">{{ __('Engagement') }}</div>
            <x-admin.nav-link route="admin.comments.index" icon="bi-chat-left-dots" label="Reacties" can="comments.moderate" />
            <x-admin.nav-link route="admin.subscribers.index" icon="bi-envelope-at" label="Abonnees" can="subscribers.manage" />
            <x-admin.nav-link route="admin.newsletters.index" icon="bi-megaphone" label="Nieuwsbrieven" can="newsletters.manage" />
        </div>
        <div class="admin-sidebar__section">
            <div class="admin-sidebar__section-label">{{ __('Beheer') }}</div>
            <x-admin.nav-link route="admin.categories.index" icon="bi-tag" label="Categorieën" can="content.manage" />
            <x-admin.nav-link route="admin.tags.index" icon="bi-tags" label="Tags" can="content.manage" />
            <x-admin.nav-link route="admin.media.index" icon="bi-images" label="Media" can="media.browse" />
            <x-admin.nav-link route="admin.trash.index" icon="bi-trash" label="Prullenbak" can="trash.manage" />
            <x-admin.nav-link route="admin.users.index" icon="bi-person-gear" label="Gebruikers" can="users.manage" />
        </div>
    </nav>
    <div class="admin-sidebar__footer">
        <button type="button" class="admin-sidebar__toggle" @click="toggleCollapse">
            <i class="bi bi-chevron-double-left"></i>
            <span class="admin-sidebar__toggle-label">{{ __('Inklappen') }}</span>
        </button>
    </div>
</aside>
<div
    class="admin-sidebar-backdrop"
    :class="{ 'is-visible': mobileOpen }"
    @click="mobileOpen = false"
    x-show="mobileOpen"
    x-transition.opacity
></div>
