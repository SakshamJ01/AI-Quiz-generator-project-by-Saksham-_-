---
name: Premium AI SaaS System
colors:
  surface: '#0b1326'
  surface-dim: '#0b1326'
  surface-bright: '#31394d'
  surface-container-lowest: '#060e20'
  surface-container-low: '#131b2e'
  surface-container: '#171f33'
  surface-container-high: '#222a3d'
  surface-container-highest: '#2d3449'
  on-surface: '#dae2fd'
  on-surface-variant: '#c7c4d8'
  inverse-surface: '#dae2fd'
  inverse-on-surface: '#283044'
  outline: '#918fa1'
  outline-variant: '#464555'
  surface-tint: '#c3c0ff'
  primary: '#c3c0ff'
  on-primary: '#1d00a5'
  primary-container: '#4f46e5'
  on-primary-container: '#dad7ff'
  inverse-primary: '#4d44e3'
  secondary: '#ddb8ff'
  on-secondary: '#490080'
  secondary-container: '#7c03d3'
  on-secondary-container: '#dfbcff'
  tertiary: '#4cd7f6'
  on-tertiary: '#003640'
  tertiary-container: '#006a7c'
  on-tertiary-container: '#93e8ff'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#e2dfff'
  primary-fixed-dim: '#c3c0ff'
  on-primary-fixed: '#0f0069'
  on-primary-fixed-variant: '#3323cc'
  secondary-fixed: '#f0dbff'
  secondary-fixed-dim: '#ddb8ff'
  on-secondary-fixed: '#2c0051'
  on-secondary-fixed-variant: '#6800b4'
  tertiary-fixed: '#acedff'
  tertiary-fixed-dim: '#4cd7f6'
  on-tertiary-fixed: '#001f26'
  on-tertiary-fixed-variant: '#004e5c'
  background: '#0b1326'
  on-background: '#dae2fd'
  surface-variant: '#2d3449'
typography:
  display-xl:
    fontFamily: Inter
    fontSize: 60px
    fontWeight: '700'
    lineHeight: 72px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 36px
    fontWeight: '600'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 30px
    fontWeight: '600'
    lineHeight: 38px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  container-max-width: 1320px
  gutter: 1.5rem
  margin-mobile: 1rem
  margin-desktop: 2rem
  stack-sm: 0.5rem
  stack-md: 1rem
  stack-lg: 2rem
---

## Brand & Style
The design system is engineered for a premium, AI-driven educational and recruitment platform. It draws inspiration from high-end developer tools and fintech leaders, emphasizing precision, speed, and clarity. The brand personality is **sophisticated, intelligent, and highly functional**, bridging the gap between academic rigor and modern technological innovation.

The visual style is **Modern Minimalist with Futuristic Accents**. It utilizes expansive white space (or deep slate space in dark mode), sharp typography, and subtle lighting effects to guide the user's focus toward the AI-generated content. The emotional response should be one of confidence and ease, reducing the cognitive load of quiz creation through a polished, reliable interface.

## Colors
The palette is rooted in a deep, professional foundation of **Dark Slate (#0F172A)**, serving as the primary canvas for the default dark mode. The primary **Indigo (#4F46E5)** and secondary **Purple (#9333EA)** are used strategically for primary actions and brand presence, often blending in gradients to signify "AI Intelligence."

**Color Application Rules:**
- **Primary Indigo:** Used for main CTAs, active states, and focus indicators.
- **Secondary Purple:** Used for supplementary AI-driven features or category distinctions.
- **Cyan Accent:** Reserved for high-interest highlights, progress bars, and "New" badges.
- **Surface Tones:** In dark mode, surfaces use subtle variations of slate to create hierarchy. In light mode, surfaces use pure white with very light cool-gray borders.

## Typography
This design system uses **Inter** exclusively to maintain a systematic, utilitarian, and highly legible appearance across all contexts. The scale is built on a tight, low-contrast hierarchy for body text to ensure readability in data-heavy views, while headlines use tighter letter-spacing and heavier weights for a more impactful, "tech-first" look.

- **Headlines:** Use Semi-Bold (600) or Bold (700) with slight negative letter-spacing to create a "locked-in" feel.
- **Body:** Use Regular (400) for general content and Medium (500) for emphasis or UI labels.
- **Caps:** Small labels (label-sm) should use uppercase with slight tracking (letter-spacing) for better scanability in navigation and table headers.

## Layout & Spacing
The layout follows a **Bootstrap 5 compatible 12-column fluid grid system**. The logic is based on an 8px rhythmic scale (represented here as a 4px base unit), ensuring alignment across all components.

**Breakpoints:**
- **Mobile (< 576px):** 4 columns, 16px margins, vertical stack for widgets.
- **Tablet (576px - 992px):** 8 columns, 24px margins, adaptive sidebars.
- **Desktop (> 992px):** 12 columns, 1320px max-width container, fixed sidebars (280px).

**Layout Philosophy:** Use generous internal padding within cards (minimum 24px) to maintain the "premium" airy feel. Group related items using the `stack-sm` or `stack-md` units to maintain clear visual associations.

## Elevation & Depth
Depth is created through **Tonal Layering** and **Ambient Shadows** rather than stark borders. In dark mode, the "base" is the darkest layer, and as elements move "closer" to the user (e.g., cards, modals), the background color becomes slightly lighter.

- **Level 0 (Base):** #0F172A (Dark Mode) / #F8FAFC (Light Mode).
- **Level 1 (Cards/Widgets):** #1E293B (Dark Mode) / #FFFFFF (Light Mode). Subtle 1px border (#334155 in dark mode / #E2E8F0 in light mode).
- **Level 2 (Modals/Popovers):** #1E293B with a high-diffusion shadow: `0 20px 25px -5px rgba(0, 0, 0, 0.5)`.
- **Glow Effects:** Use primary indigo with low opacity (10-15%) as an outer glow for focused inputs or active AI states to simulate a futuristic "powered-on" aesthetic.

## Shapes
The design system employs a **Rounded (Level 2)** shape language to soften the technical nature of the AI, making it feel approachable and human-centric.

- **Standard Components:** 0.5rem (8px) for buttons, small inputs, and chips.
- **Container Elements:** 1rem (16px) for cards, dashboard widgets, and modals (the core "ROUND_SIXTEEN" aesthetic).
- **Interactive States:** On hover, clickable cards may transition slightly (2px lift) while maintaining their radius.

## Components

### Navigation
- **Top Bar:** 64px height, semi-transparent background with a 4px backdrop blur. Includes breadcrumbs and global search.
- **Sidebar:** 280px width, collapsible. Uses ghost-style nav items with a 4px left-border active indicator in Indigo.

### Buttons
- **Solid:** Primary Indigo background with white text. Soft hover transition to Purple.
- **Outline:** 1px border using Indigo. Subtle Indigo tint background on hover.
- **Ghost:** No border or background. Indigo text. Becomes light gray/slate on hover.

### Inputs & Fields
- **Search/Text:** Minimal 1px border (#334155). On focus, the border turns Indigo with a 4px Indigo outer glow.
- **Selection:** Checkboxes and Radios use the primary Indigo for the checked state.

### Cards & Tables
- **Cards:** 16px radius, Level 1 surface elevation. Includes a footer area with a subtle top border for actions.
- **Tables:** No vertical borders. Horizontal borders are low-contrast. Table headers use `label-sm` (all caps) and are sticky.

### AI Feedback
- **Loading:** Pulsing Indigo-to-Cyan linear gradient for progress bars.
- **Empty States:** Centered, gray-scale icons with a single Primary-colored CTA to "Create First Quiz."