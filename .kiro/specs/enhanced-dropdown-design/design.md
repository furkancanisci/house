# Enhanced Dropdown Design Document

## Overview

This design document outlines the enhancement of the document type dropdown ("نوع التابو") in the AddProperty form. The goal is to create a modern, smooth, and visually appealing dropdown that displays document types with their descriptions, maintains consistency with the application's design system, and provides an excellent user experience across all devices.

## Architecture

### Component Structure
```
EnhancedDocumentTypeSelect
├── SelectTrigger (Enhanced)
│   ├── Icon (File icon with animation)
│   ├── Selected Value Display
│   └── Chevron Icon (Animated)
├── SelectContent (Enhanced)
│   ├── Loading State Component
│   ├── Error State Component
│   └── Options List
│       └── SelectItem (Enhanced)
│           ├── Document Type Icon
│           ├── Primary Text (Name)
│           ├── Secondary Text (Description)
│           └── Selection Indicator
└── Tooltip Component (For truncated descriptions)
```

### Design System Integration
- Uses existing color palette: `#067977` (primary), purple accents, and gray scales
- Maintains consistent border radius (rounded-xl = 12px)
- Follows existing spacing patterns (padding, margins)
- Uses application typography hierarchy

## Components and Interfaces

### 1. Enhanced Select Trigger

**Visual Design:**
- Height: 56px (h-14) for better touch targets
- Border: 2px solid with gradient hover effects
- Background: White with subtle gradient overlay
- Shadow: Soft drop shadow on hover/focus
- Animation: Smooth border color transitions (300ms ease)

**Interactive States:**
```css
Default: border-gray-200, bg-white
Hover: border-purple-300, bg-gradient-to-r from-purple-50 to-pink-50
Focus: border-purple-500, ring-4 ring-purple-100
Active: border-purple-600, slight scale transform
```

**Content Layout:**
```
[File Icon] [Selected Text or Placeholder] [Chevron Icon]
```

### 2. Enhanced Select Content

**Container Design:**
- Background: White with backdrop blur effect
- Border: 1px solid gray-200 with subtle shadow
- Border radius: 16px (rounded-2xl)
- Max height: 320px with smooth scrolling
- Animation: Slide down with fade-in (200ms ease-out)

**Positioning:**
- Smart positioning to avoid viewport edges
- Mobile-responsive positioning
- Z-index management for proper layering

### 3. Enhanced Select Items

**Layout Structure:**
```
┌─────────────────────────────────────────┐
│ [Icon] [Document Type Name]             │
│        [Description Text]               │
│                              [Indicator]│
└─────────────────────────────────────────┘
```

**Visual Specifications:**
- Padding: 16px horizontal, 12px vertical
- Min height: 64px for touch accessibility
- Typography: 
  - Name: text-base font-semibold
  - Description: text-sm text-gray-600
- Icons: 20x20px with color coding
- Selection indicator: Checkmark icon (16x16px)

**Interactive States:**
```css
Default: bg-white, text-gray-900
Hover: bg-gradient-to-r from-purple-50 to-pink-50, slight scale
Selected: bg-purple-100, border-left-4 border-purple-500
Focus: ring-2 ring-purple-200
```

### 4. Document Type Icons and Colors

**Icon Mapping:**
- Regular Title: `FileText` icon, color: `#067977`
- Updated Title: `RefreshCw` icon, color: `#10b981` (emerald-500)
- Agricultural Title: `Leaf` icon, color: `#22c55e` (green-500)
- Construction Land: `Building` icon, color: `#f59e0b` (amber-500)
- Temporary Title: `Clock` icon, color: `#ef4444` (red-500)
- Family Title: `Users` icon, color: `#8b5cf6` (violet-500)

### 5. Loading and Error States

**Loading State:**
- Skeleton animation for dropdown items
- Spinning icon in trigger
- Shimmer effect for text placeholders
- Duration: Smooth 1.5s loop animation

**Error State:**
- Error icon with red color scheme
- Fallback to static document types
- Retry button with hover effects
- Clear error messaging

## Data Models

### PropertyDocumentType Interface
```typescript
interface PropertyDocumentType {
  id: number;
  name: string;
  description?: string;
  sort_order: number;
  icon?: string; // New field for icon mapping
  color?: string; // New field for color theming
}
```

### Enhanced Dropdown Props
```typescript
interface EnhancedDocumentTypeSelectProps {
  value?: string;
  onValueChange: (value: string) => void;
  placeholder?: string;
  disabled?: boolean;
  loading?: boolean;
  error?: string | null;
  documentTypes: PropertyDocumentType[];
  className?: string;
  showDescriptions?: boolean;
  maxHeight?: number;
}
```

## Animation Specifications

### 1. Dropdown Open/Close Animation
```css
@keyframes slideDownAndFade {
  from {
    opacity: 0;
    transform: translateY(-8px) scale(0.96);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes slideUpAndFade {
  from {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
  to {
    opacity: 0;
    transform: translateY(-8px) scale(0.96);
  }
}
```

### 2. Hover Animations
- Scale transform: `scale(1.02)` on hover
- Color transitions: 300ms ease-in-out
- Shadow transitions: 200ms ease-out

### 3. Selection Animation
- Checkmark fade-in: 150ms ease-out
- Background color transition: 200ms ease-in-out
- Border animation: 250ms ease-out

## Responsive Design

### Desktop (≥1024px)
- Full dropdown width with optimal spacing
- Hover effects enabled
- Tooltip positioning: right or left based on space

### Tablet (768px - 1023px)
- Adjusted padding and font sizes
- Touch-friendly targets (minimum 44px)
- Simplified hover states

### Mobile (≤767px)
- Full-width dropdown on small screens
- Larger touch targets (minimum 48px)
- Bottom sheet style for better mobile UX
- Simplified animations for performance

## Accessibility Features

### Keyboard Navigation
- Tab navigation through options
- Arrow keys for option selection
- Enter/Space for selection
- Escape to close dropdown

### Screen Reader Support
- Proper ARIA labels and descriptions
- Role attributes for dropdown elements
- Live region announcements for state changes
- Description text read aloud

### Focus Management
- Clear focus indicators
- Focus trapping within dropdown
- Return focus to trigger on close
- Skip links for keyboard users

## Performance Considerations

### Optimization Strategies
- Virtual scrolling for large lists (>50 items)
- Debounced search functionality
- Lazy loading of descriptions
- Memoized components to prevent re-renders

### Animation Performance
- CSS transforms instead of layout changes
- GPU acceleration with `will-change` property
- Reduced motion support for accessibility
- Frame rate optimization (60fps target)

## Error Handling

### API Error Scenarios
1. **Network Failure**: Show fallback document types with retry option
2. **Invalid Response**: Display error message with fallback data
3. **Timeout**: Show loading state with timeout message
4. **Authentication Error**: Redirect to login or show auth error

### User Experience During Errors
- Graceful degradation to static data
- Clear error messaging in Arabic
- Retry mechanisms with exponential backoff
- Offline support with cached data

## Testing Strategy

### Unit Tests
- Component rendering with different props
- Event handling (selection, hover, keyboard)
- Animation state management
- Error boundary testing

### Integration Tests
- API integration with mock responses
- Form integration and validation
- Responsive behavior across breakpoints
- Accessibility compliance testing

### Visual Regression Tests
- Screenshot comparisons for different states
- Animation frame testing
- Cross-browser compatibility
- Mobile device testing

### Performance Tests
- Render time benchmarks
- Animation smoothness metrics
- Memory usage monitoring
- Bundle size impact analysis