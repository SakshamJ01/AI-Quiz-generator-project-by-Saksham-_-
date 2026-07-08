---
name: Intelligent Academic Minimal
colors:
  surface: '#faf8ff'
  surface-dim: '#d9d9e5'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f3fe'
  surface-container: '#ededf9'
  surface-container-high: '#e7e7f3'
  surface-container-highest: '#e1e2ed'
  on-surface: '#191b23'
  on-surface-variant: '#434655'
  inverse-surface: '#2e3039'
  inverse-on-surface: '#f0f0fb'
  outline: '#737686'
  outline-variant: '#c3c6d7'
  surface-tint: '#0053db'
  primary: '#004ac6'
  on-primary: '#ffffff'
  primary-container: '#2563eb'
  on-primary-container: '#eeefff'
  inverse-primary: '#b4c5ff'
  secondary: '#505f76'
  on-secondary: '#ffffff'
  secondary-container: '#d0e1fb'
  on-secondary-container: '#54647a'
  tertiary: '#943700'
  on-tertiary: '#ffffff'
  tertiary-container: '#bc4800'
  on-tertiary-container: '#ffede6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea8'
  secondary-fixed: '#d3e4fe'
  secondary-fixed-dim: '#b7c8e1'
  on-secondary-fixed: '#0b1c30'
  on-secondary-fixed-variant: '#38485d'
  tertiary-fixed: '#ffdbcd'
  tertiary-fixed-dim: '#ffb596'
  on-tertiary-fixed: '#360f00'
  on-tertiary-fixed-variant: '#7d2d00'
  background: '#faf8ff'
  on-background: '#191b23'
  surface-variant: '#e1e2ed'
typography:
  display:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '800'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.3'
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '700'
    lineHeight: '1.3'
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: 0.05em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: '1.2'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  2xl: 48px
  3xl: 64px
  container-max: 1280px
  gutter: 24px
---

## Brand & Style
The design system is built for an AI-powered educational tool, prioritizing clarity, speed, and focus. The brand personality is professional, reliable, and academic without being overly formal. 

The style is **Minimalist-Modern**, drawing heavily from utility-first principles. It avoids decorative flourishes like glassmorphism or heavy gradients to ensure the user's cognitive load is reserved for the quiz content. It utilizes high-quality typography and generous whitespace to create a sense of calm and organization.

## Colors
This design system uses a high-contrast palette optimized for readability. 
- **Primary Blue** is used for the main calls to action and active states. 
- **Light Gray Background** provides a soft canvas that reduces eye strain compared to pure white, while **White Surfaces** are used to elevate content containers.
- **Success and Danger** colors are reserved for feedback loops, such as correct/incorrect quiz answers and destructive actions.
- **Neutral Scales** (Slate/Gray) are used for borders and secondary text to maintain a hierarchy of information.

## Typography
The system relies exclusively on **Inter**, a typeface designed for user interfaces. 
- **Headlines** use tighter letter spacing and heavier weights to create a strong visual anchor.
- **Body Text** uses a standard weight with a generous line height (1.6) to ensure long-form quiz questions and explanations are easy to digest.
- **Labels** use a medium or semi-bold weight to distinguish metadata from content.

## Layout & Spacing
The design system follows a **12-column fluid grid** model. 
- **Margins:** 24px on desktop, scaling down to 16px on mobile.
- **Gutter:** A consistent 24px gutter maintains separation between cards and grid items.
- **Spacing Rhythm:** Based on a 4px baseline, with most components using 16px (md) or 24px (lg) for internal padding.
- **Maximum Width:** Content is capped at 1280px to prevent line lengths from becoming unreadable on ultra-wide monitors.

## Elevation & Depth
Depth is created through **Tonal Layering** and **Subtle Shadows**. 
- **Level 0 (Background):** Light Gray (#F3F4F6) for the overall page canvas.
- **Level 1 (Surface):** White (#FFFFFF) for cards and main content areas.
- **Shadows:** Use a single, subtle shadow style (`shadow-sm`) for interactive cards: `0 1px 2px 0 rgba(0, 0, 0, 0.05)`. 
- **Borders:** Surfaces should have a 1px solid border (#E5E7EB) to provide definition against the light gray background.

## Shapes
The design system uses a **Rounded** shape language to feel modern and accessible. 
- **Standard (8px):** Used for buttons, input fields, and cards.
- **Large (16px):** Used for larger feature containers or modal windows.
- **Pill:** Reserved exclusively for tags or status indicators (e.g., "Active", "Draft").

## Components
- **Buttons:** 
  - *Primary:* Solid Blue (#2563EB) with white text. Hover state: darken to #1D4ED8.
  - *Secondary:* Gray outline or light gray fill with slate text.
- **Cards:** White background, 1px border (#E5E7EB), and 8px border radius. Padding should be 24px.
- **Input Fields:** 8px radius, 1px border (#D1D5DB). On focus, the border changes to Primary Blue with a subtle 3px outer glow.
- **Navigation:** A clean top bar with a white background, a subtle bottom border, and 16px vertical padding. Use Primary Blue for the active link state.
- **Quiz Specifics:**
  - *Option Tiles:* Large, clickable surfaces with 8px radius. When selected, the border should thicken and change to Primary Blue.
  - *Progress Bar:* A simple 8px tall track (Light Gray) with a Primary Blue fill.