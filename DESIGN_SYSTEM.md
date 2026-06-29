# Bapperida Design System - Component Library Documentation

## Overview

This is a comprehensive, reusable Blade component library for the Bapperida Mail Records System. All components support:

- ✅ **Dark Mode** - Automatic theme switching
- ✅ **Light Mode** - Professional slate/white palette
- ✅ **Accessibility** - ARIA attributes and keyboard support
- ✅ **Validation** - Error states and hints
- ✅ **Responsive** - Mobile-first design
- ✅ **No Duplication** - Fully composable components

## Theme Colors

### Dark Mode

- **Background**: `#050b1a` (navy-900)
- **Secondary**: `#0a1633` (navy-800)
- **Accent**: `#EAB308` (gold-500)
- **Text**: `slate-100`

### Light Mode

- **Background**: `slate-50`
- **Cards**: `white`
- **Accent**: `#EAB308` (gold-500)
- **Text**: `slate-900`

## Component Categories

### Form Components

#### x-input

Basic text input with validation support.

```blade
<x-form.input
    name="email"
    type="email"
    label="Email Address"
    placeholder="name@example.com"
    error="{{ $errors->first('email') }}"
    required
    hint="We'll never share your email"
/>
```

**Props:**

- `name` - Input name
- `id` - Input ID (defaults to name)
- `type` - Input type (text, email, password, number, date, etc.)
- `value` - Current value
- `placeholder` - Placeholder text
- `label` - Label text
- `error` - Error message
- `required` - Show required indicator
- `hint` - Help text
- `disabled` - Disable input
- `readonly` - Make readonly
- `class` - Additional CSS classes

#### x-textarea

Multi-line text input.

```blade
<x-form.textarea
    name="message"
    label="Message"
    rows="5"
    error="{{ $errors->first('message') }}"
    placeholder="Enter your message..."
/>
```

**Props:** Same as input, plus:

- `rows` - Number of visible rows

#### x-select

Dropdown select input.

```blade
<x-form.select
    name="department"
    label="Department"
    :options="[
        'sales' => 'Sales',
        'marketing' => 'Marketing',
        'engineering' => 'Engineering',
    ]"
    placeholder="Choose a department..."
    error="{{ $errors->first('department') }}"
/>
```

**Props:**

- `name` - Input name
- `options` - Array of options (key => label)
- `value` - Selected value
- `multiple` - Allow multiple selection
- `placeholder` - Placeholder text
- `label`, `error`, `hint`, `required` - Same as input

#### x-checkbox

Checkbox input with label.

```blade
<x-form.checkbox
    name="accept_terms"
    label="I accept the terms and conditions"
    value="1"
    checked="{{ old('accept_terms') }}"
    error="{{ $errors->first('accept_terms') }}"
/>
```

**Props:**

- `name`, `value`, `label`, `error`, `hint`, `required`
- `checked` - Is checked
- `disabled` - Disable checkbox

#### x-radio

Radio button input.

```blade
<x-form.radio
    name="role"
    value="admin"
    label="Administrator"
    checked="{{ old('role') === 'admin' }}"
/>
```

**Props:** Same as checkbox

#### x-switch

Toggle switch component.

```blade
<x-form.switch
    name="is_active"
    label="Active"
    checked="{{ $user->is_active }}"
/>
```

Uses Alpine.js for interactivity.

#### x-file-upload

Drag-and-drop file upload.

```blade
<x-form.file-upload
    name="attachment"
    label="Upload Document"
    accept=".pdf,.doc,.docx"
    maxSize="10MB"
    hint="Accepted formats: PDF, DOC, DOCX"
/>
```

**Props:**

- `name` - Input name
- `accept` - File types to accept
- `multiple` - Allow multiple files
- `maxSize` - Max file size display
- `label`, `error`, `required`, `hint`

---

### Button Component

#### x-button

Versatile button component.

```blade
<!-- Primary Button -->
<x-button>Save</x-button>

<!-- Secondary Button -->
<x-button variant="secondary">Cancel</x-button>

<!-- Danger Button -->
<x-button variant="danger">Delete</x-button>

<!-- Loading State -->
<x-button loading>Saving...</x-button>

<!-- Icon Button -->
<x-button icon="<path... />">Edit</x-button>

<!-- Icon Only -->
<x-button icon-only icon="<path... />" />
```

**Props:**

- `type` - Button type (button, submit, reset)
- `variant` - primary, secondary, danger, ghost, outline
- `size` - sm, md, lg
- `loading` - Show loading spinner
- `disabled` - Disable button
- `icon` - SVG icon HTML
- `iconOnly` - Hide text, show icon only
- `class` - Additional CSS classes

**Variants:**

- `primary` - Gold background, navy text (default)
- `secondary` - Slate background
- `danger` - Red background, white text
- `ghost` - Transparent with hover
- `outline` - Gold border with transparent background

---

### Layout Components

#### x-card

Container for content.

```blade
<x-card hoverable>
    <div class="p-6">
        <h3 class="font-bold">Card Title</h3>
        <p>Card content goes here.</p>
    </div>
</x-card>
```

**Props:**

- `hoverable` - Add hover effect
- `clickable` - Add cursor pointer
- `class` - Additional CSS classes

#### x-page-header

Page title section.

```blade
<x-page-header
    title="Mail Records"
    description="Manage incoming and outgoing letters"
    icon="<path stroke-linecap='round'... />"
>
    <x-button>New Record</x-button>
</x-page-header>
```

**Props:**

- `title` - Page title
- `description` - Page description
- `icon` - SVG icon HTML
- `class` - Additional CSS classes

#### x-breadcrumb

Navigation breadcrumbs.

```blade
<x-breadcrumb :items="[
    ['label' => 'Dashboard', 'href' => route('dashboard')],
    ['label' => 'Mail Records', 'href' => route('mail.index')],
    ['label' => 'View Record'],
]" />
```

**Props:**

- `items` - Array of breadcrumb items [label, href]
- `class` - Additional CSS classes

---

### Feedback Components

#### x-alert

Alert messages.

```blade
<!-- Success Alert -->
<x-alert type="success" title="Success!" dismissible>
    Your changes have been saved successfully.
</x-alert>

<!-- Error Alert -->
<x-alert type="error" title="Error">
    Something went wrong. Please try again.
</x-alert>

<!-- Warning Alert -->
<x-alert type="warning" dismissible>
    This action cannot be undone.
</x-alert>

<!-- Info Alert -->
<x-alert type="info" title="Information">
    Please note this important information.
</x-alert>
```

**Props:**

- `type` - info, success, warning, error
- `title` - Alert title
- `dismissible` - Show close button
- `icon` - Custom icon HTML
- `class` - Additional CSS classes

#### x-toast

Temporary notification toast.

```blade
<x-toast type="success" message="Record saved successfully!" duration="3000" />
<x-toast type="error" duration="5000">Custom error message</x-toast>
```

**Props:**

- `type` - success, error, warning, info
- `message` - Toast message
- `duration` - Show duration in ms (0 = never auto-close)
- `class` - Additional CSS classes

#### x-badge

Small label component.

```blade
<x-badge color="gold">New</x-badge>
<x-badge color="green" variant="outline">Active</x-badge>
<x-badge color="red" size="sm">Critical</x-badge>
```

**Props:**

- `color` - gray, gold, red, green, blue, slate
- `variant` - solid, outline
- `size` - sm, md, lg
- `rounded` - Rounded corners
- `class` - Additional CSS classes

#### x-loading

Loading spinner.

```blade
<x-loading text="Loading..." size="md" />
```

**Props:**

- `text` - Loading text
- `size` - sm, md, lg
- `class` - Additional CSS classes

#### x-empty-state

Empty state placeholder.

```blade
<x-empty-state
    icon="<path... />"
    title="No Records Found"
    description="There are no mail records yet."
>
    <x-button>Create First Record</x-button>
</x-empty-state>
```

**Props:**

- `icon` - SVG icon HTML
- `title` - Empty state title
- `description` - Empty state description
- `action` - Action slot content
- `class` - Additional CSS classes

---

### Data Components

#### x-table, x-table.head, x-table.body, x-table.row, x-table.cell, x-table.header-cell

Table structure.

```blade
<x-table>
    <x-table.head>
        <x-table.row>
            <x-table.header-cell sortable>Name</x-table.header-cell>
            <x-table.header-cell>Email</x-table.header-cell>
            <x-table.header-cell>Status</x-table.header-cell>
        </x-table.row>
    </x-table.head>

    <x-table.body>
        @foreach ($users as $user)
            <x-table.row>
                <x-table.cell>{{ $user->name }}</x-table.cell>
                <x-table.cell>{{ $user->email }}</x-table.cell>
                <x-table.cell>
                    <x-badge color="green">{{ $user->status }}</x-badge>
                </x-table.cell>
            </x-table.row>
        @endforeach
    </x-table.body>
</x-table>
```

**Table Props:**

- `striped` - Add striped rows
- `hover` - Add hover effect
- `class` - Additional CSS classes

**Header Cell Props:**

- `sortable` - Show sort indicator
- `sortDir` - Sort direction (asc, desc)

#### x-pagination

Pagination links.

```blade
<x-pagination :paginator="$users" />
```

**Props:**

- `paginator` - Laravel paginator instance
- `class` - Additional CSS classes

#### x-stat-card

Statistics card.

```blade
<x-stat-card
    title="Total Records"
    value="1,234"
    subtitle="↑ 12% from last month"
    trend="up"
    trendPercent="12"
    icon="<path... />"
/>
```

**Props:**

- `title` - Card title
- `value` - Stat value
- `subtitle` - Subtitle text
- `icon` - SVG icon HTML
- `trend` - up, down
- `trendPercent` - Trend percentage
- `class` - Additional CSS classes

---

### Interactive Components

#### x-avatar

User avatar.

```blade
<!-- Image Avatar -->
<x-avatar src="{{ $user->avatar_url }}" name="John Doe" size="md" />

<!-- Initials Avatar -->
<x-avatar name="Jane Smith" size="lg" />
```

**Props:**

- `src` - Avatar image URL
- `name` - User name (for initials)
- `initials` - Override initials
- `size` - sm, md, lg, xl
- `class` - Additional CSS classes

#### x-dropdown, x-dropdown.trigger, x-dropdown.menu, x-dropdown.item

Dropdown menu.

```blade
<x-dropdown>
    <x-dropdown.trigger>
        <x-button icon-only>
            <svg class="w-5 h-5"><!-- ... --></svg>
        </x-button>
    </x-dropdown.trigger>

    <x-dropdown.menu align="right">
        <x-dropdown.item href="/profile">Profile</x-dropdown.item>
        <x-dropdown.item href="/settings">Settings</x-dropdown.item>
        <x-dropdown.item href="/logout" method="post">Logout</x-dropdown.item>
    </x-dropdown.menu>
</x-dropdown>
```

**Trigger Props:**

- `class` - Additional CSS classes

**Menu Props:**

- `align` - left, right
- `class` - Additional CSS classes

**Item Props:**

- `href` - Link URL
- `type` - button, submit
- `icon` - SVG icon HTML
- `class` - Additional CSS classes

#### x-modal

Modal dialog.

```blade
<x-modal open="true" title="Create Record" max-width="max-w-md">
    <form action="/records" method="post">
        @csrf
        <x-form.input name="title" label="Title" />
        <div class="mt-6 flex gap-3">
            <x-button variant="secondary">Cancel</x-button>
            <x-button type="submit">Create</x-button>
        </div>
    </form>
</x-modal>
```

**Props:**

- `open` - Is open initially
- `title` - Modal title
- `maxWidth` - max-w-sm, max-w-md, max-w-lg, max-w-xl
- `closeButton` - Show close button
- `backdrop` - Show backdrop
- `class` - Additional CSS classes

#### x-confirm-dialog

Confirmation dialog.

```blade
<x-confirm-dialog
    title="Delete Record?"
    message="This action cannot be undone."
    confirmText="Delete"
    confirmVariant="danger"
    @confirm="deleteRecord()"
    @cancel="close()"
/>
```

**Props:**

- `title` - Dialog title
- `message` - Confirmation message
- `confirmText` - Confirm button text
- `cancelText` - Cancel button text
- `confirmVariant` - primary, danger
- `open` - Is open initially
- `class` - Additional CSS classes

---

## Usage Examples

### Complete Form Example

```blade
<x-card class="max-w-2xl mx-auto">
    <div class="p-6">
        <x-page-header title="Create Mail Record" />

        <form action="/records" method="post" enctype="multipart/form-data" class="space-y-6 mt-6">
            @csrf

            <x-form.input
                name="letter_number"
                label="Letter Number"
                error="{{ $errors->first('letter_number') }}"
                required
            />

            <x-form.select
                name="type"
                label="Letter Type"
                :options="config('letter.types')"
                error="{{ $errors->first('type') }}"
                required
            />

            <x-form.textarea
                name="subject"
                label="Subject"
                error="{{ $errors->first('subject') }}"
                required
            />

            <x-form.file-upload
                name="attachment"
                label="Attachment (PDF)"
                accept=".pdf"
                error="{{ $errors->first('attachment') }}"
            />

            <x-form.checkbox
                name="is_urgent"
                label="Mark as urgent"
                value="1"
            />

            <div class="flex gap-3">
                <x-button variant="secondary" href="/records">Cancel</x-button>
                <x-button type="submit">Create Record</x-button>
            </div>
        </form>
    </div>
</x-card>
```

### Dashboard Example

```blade
<x-page-header
    title="Dashboard"
    description="Welcome back to Bapperida"
/>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card
        title="Total Records"
        value="1,234"
        trend="up"
        trendPercent="12"
        icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' />"
    />
    <!-- More stat cards -->
</div>

<x-card class="mb-8">
    <div class="p-6">
        <h3 class="text-lg font-bold mb-4">Recent Records</h3>
        <x-table>
            <x-table.head>
                <x-table.row>
                    <x-table.header-cell>Number</x-table.header-cell>
                    <x-table.header-cell>Subject</x-table.header-cell>
                    <x-table.header-cell>Status</x-table.header-cell>
                    <x-table.header-cell>Date</x-table.header-cell>
                </x-table.row>
            </x-table.head>
            <x-table.body>
                @forelse ($records as $record)
                    <x-table.row>
                        <x-table.cell>{{ $record->letter_number }}</x-table.cell>
                        <x-table.cell>{{ $record->subject }}</x-table.cell>
                        <x-table.cell>
                            <x-badge :color="$record->status_color">
                                {{ $record->status_label }}
                            </x-badge>
                        </x-table.cell>
                        <x-table.cell>{{ $record->created_at->format('M d, Y') }}</x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="4" class="text-center py-8">
                            <x-empty-state
                                title="No records found"
                                description="There are no mail records yet."
                            />
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>

        <div class="mt-6">
            <x-pagination :paginator="$records" />
        </div>
    </div>
</x-card>
```

---

## Accessibility Features

All components include:

- ✅ **ARIA Labels** - Proper aria-labels and roles
- ✅ **Keyboard Navigation** - Full keyboard support
- ✅ **Focus Management** - Clear focus indicators
- ✅ **Semantic HTML** - Proper HTML structure
- ✅ **Color Contrast** - WCAG AA compliant
- ✅ **Error Messages** - Clear validation messages
- ✅ **Screen Reader Support** - Proper announcements

---

## Customization

### Theme Colors

Update `tailwind.config.js` to customize colors:

```javascript
colors: {
    gold: { 500: '#YOUR_COLOR' },
    navy: { 900: '#YOUR_COLOR' },
    // ...
}
```

### Dark Mode

Dark mode is automatically applied when the `dark` class is on the `<html>` element.

Livewire and Flux can handle automatic theme switching.

### Custom Styling

Add custom classes via the `class` prop on any component:

```blade
<x-button class="custom-class">Custom Button</x-button>
```

---

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers

---

## Notes

- Components use Alpine.js for interactivity (switches, dropdowns, modals)
- All components are fully reusable and composable
- No JavaScript frameworks required (except Alpine.js)
- All styling uses Tailwind CSS v4
- Components support both light and dark modes automatically

---

**Last Updated:** June 27, 2026  
**Version:** 1.0.0
