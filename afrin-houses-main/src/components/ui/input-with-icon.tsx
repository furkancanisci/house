import * as React from "react"
import { cn } from "@/lib/utils"
import { useTranslation } from "react-i18next"

export interface InputWithIconProps extends React.ComponentProps<"input"> {
  icon?: React.ComponentType<{ className?: string }>
  iconPosition?: 'start' | 'end'
  containerClassName?: string
  iconClassName?: string
  dir?: 'ltr' | 'rtl' | 'auto'
}

/**
 * RTL-aware Input component with proper icon positioning and spacing
 * 
 * Features:
 * - Automatic direction detection from i18n context
 * - Logical padding properties (padding-inline-start/end)
 * - Proper icon positioning for both LTR and RTL
 * - Accessible design with screen reader support
 * - Consistent spacing regardless of language
 */
const InputWithIcon = React.forwardRef<HTMLInputElement, InputWithIconProps>(
  ({ 
    className, 
    icon: Icon, 
    iconPosition = 'start',
    containerClassName,
    iconClassName,
    dir = 'auto',
    type, 
    ...props 
  }, ref) => {
    const { i18n } = useTranslation()
    
    // Determine direction
    const isRTL = dir === 'auto' ? i18n.language === 'ar' : dir === 'rtl'
    
    // Calculate icon position based on RTL and iconPosition prop
    const shouldIconBeOnLeft = isRTL ? iconPosition === 'end' : iconPosition === 'start'
    
    // Icon positioning classes - using Tailwind directional classes for better browser support
    const iconPositionClass = shouldIconBeOnLeft
      ? isRTL ? 'right-4' : 'left-4'
      : isRTL ? 'left-4' : 'right-4'
    
    // Padding classes - using directional padding for RTL support
    const paddingClass = Icon
      ? shouldIconBeOnLeft
        ? isRTL ? 'pr-14 pl-4' : 'pl-14 pr-4'
        : isRTL ? 'pl-14 pr-4' : 'pr-14 pl-4'
      : 'px-4'

    if (!Icon) {
      return (
        <input
          type={type}
          className={cn(
            "flex h-9 w-full rounded-md border border-zinc-200 bg-transparent py-1 text-base text-zinc-900 dark:text-zinc-50 shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-zinc-950 placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm dark:border-zinc-800 dark:file:text-zinc-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300",
            paddingClass,
            className
          )}
          dir={isRTL ? 'rtl' : 'ltr'}
          ref={ref}
          {...props}
        />
      )
    }

    return (
      <div 
        className={cn("relative", containerClassName)}
        dir={isRTL ? 'rtl' : 'ltr'}
      >
        <Icon 
          className={cn(
            "absolute top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none z-10",
            iconPositionClass,
            iconClassName
          )}
          aria-hidden="true"
        />
        <input
          type={type}
          className={cn(
            "flex h-9 w-full rounded-md border border-zinc-200 bg-transparent py-1 text-base text-zinc-900 dark:text-zinc-50 shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-zinc-950 placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm dark:border-zinc-800 dark:file:text-zinc-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300",
            paddingClass,
            className
          )}
          ref={ref}
          {...props}
        />
      </div>
    )
  }
)

InputWithIcon.displayName = "InputWithIcon"

export { InputWithIcon }