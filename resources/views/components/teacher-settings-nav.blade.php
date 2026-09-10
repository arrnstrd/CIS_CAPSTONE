@php
    $activeTab = match (request()->route()->getName()) {
        'teacher.settings.profile' => 'profile',
        'teacher.settings.notifications' => 'notifications',
        'teacher.settings.dashboard' => 'dashboard',
        'teacher.settings.security' => 'security',
        default => 'profile',
    };
@endphp

<div class="teacher-settings-nav">
    <div class="teacher-settings-nav__header">
        <div class="teacher-settings-nav__eyebrow">Settings</div>
        <div class="teacher-settings-nav__subtitle">Manage your account preferences</div>
    </div>
    <nav class="teacher-settings-nav__list" role="navigation" aria-label="Settings sections">
        <a href="{{ route('teacher.settings.profile') }}" 
           class="teacher-settings-nav__item {{ $activeTab === 'profile' ? 'active' : '' }}"
           role="tab"
           aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}">
            <i class="fa-solid fa-user"></i>
            <div class="teacher-settings-nav__item-content">
                <span class="teacher-settings-nav__item-title">Profile</span>
                <span class="teacher-settings-nav__item-desc">View and manage your profile information</span>
            </div>
        </a>

        <a href="{{ route('teacher.settings.notifications') }}" 
           class="teacher-settings-nav__item {{ $activeTab === 'notifications' ? 'active' : '' }}"
           role="tab"
           aria-selected="{{ $activeTab === 'notifications' ? 'true' : 'false' }}">
            <i class="fa-solid fa-bell"></i>
            <div class="teacher-settings-nav__item-content">
                <span class="teacher-settings-nav__item-title">Notifications</span>
                <span class="teacher-settings-nav__item-desc">Manage notification preferences</span>
            </div>
        </a>


        <a href="{{ route('teacher.settings.dashboard') }}" 
           class="teacher-settings-nav__item {{ $activeTab === 'dashboard' ? 'active' : '' }}"
           role="tab"
           aria-selected="{{ $activeTab === 'dashboard' ? 'true' : 'false' }}">
            <i class="fa-solid fa-sliders"></i>
            <div class="teacher-settings-nav__item-content">
                <span class="teacher-settings-nav__item-title">Dashboard Preferences</span>
                <span class="teacher-settings-nav__item-desc">Customize dashboard layout</span>
            </div>
        </a>

        <a href="{{ route('teacher.settings.security') }}" 
           class="teacher-settings-nav__item {{ $activeTab === 'security' ? 'active' : '' }}"
           role="tab"
           aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}">
            <i class="fa-solid fa-shield-halved"></i>
            <div class="teacher-settings-nav__item-content">
                <span class="teacher-settings-nav__item-title">Security</span>
                <span class="teacher-settings-nav__item-desc">Manage password and security</span>
            </div>
        </a>
    </nav>
</div>

<style>
.teacher-settings-nav {
    background: white;
    border-right: 1px solid #e2e8f0;
    padding: 0;
    flex-shrink: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.teacher-settings-nav__header {
    padding: 0.85rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.teacher-settings-nav__eyebrow {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 0.25rem;
}

.teacher-settings-nav__subtitle {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

.teacher-settings-nav__list {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow-y: auto;
}

.teacher-settings-nav__item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 1rem 1.5rem;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
}

.teacher-settings-nav__item:hover {
    background: #f1f5f9;
    color: #334155;
}

.teacher-settings-nav__item.active {
    background: #eff6ff;
    color: #1e40af;
    border-left-color: #1e40af;
    font-weight: 600;
}

.teacher-settings-nav__item i {
    width: 20px;
    text-align: center;
    margin-top: 2px;
}

.teacher-settings-nav__item-content {
    flex: 1;
    min-width: 0;
}

.teacher-settings-nav__item-title {
    display: block;
    font-weight: 500;
    color: inherit;
    margin-bottom: 0.25rem;
}

.teacher-settings-nav__item-desc {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    line-height: 1.3;
}

/* Responsive */
@media (max-width: 768px) {
    .teacher-settings-nav {
        border-right: none;
        border-bottom: 1px solid #e2e8f0;
        padding: 0;
    }
    
    .teacher-settings-nav__header {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .teacher-settings-nav__list {
        flex-direction: row;
        overflow-x: auto;
        padding: 0 1rem;
    }
    
    .teacher-settings-nav__item {
        flex-shrink: 0;
        padding: 0.75rem 1rem;
        white-space: nowrap;
        border-left: none;
        border-bottom: 3px solid transparent;
    }
    
    .teacher-settings-nav__item.active {
        border-left: none;
        border-bottom-color: #1e40af;
    }
    
    .teacher-settings-nav__item-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.25rem;
    }
    
    .teacher-settings-nav__item-title {
        margin-bottom: 0;
        font-size: 0.8rem;
    }
    
    .teacher-settings-nav__item-desc {
        display: none;
    }
}
</style>

