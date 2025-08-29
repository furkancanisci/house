import { Toaster as Sonner } from "sonner"
import { X } from "lucide-react"

type ToasterProps = React.ComponentProps<typeof Sonner>

const Toaster = ({ ...props }: ToasterProps) => {
  // Following Jakob's Law: Use familiar toast patterns from popular apps
  return (
    <Sonner
      className="toaster group"
      position="top-right"
      expand={false}
      richColors
      closeButton
      duration={5000}
      toastOptions={{
        className: "group",
        classNames: {
          // Clean, modern toast design following Material Design patterns
          toast:
            "group relative flex items-center justify-between overflow-hidden rounded-lg border bg-white p-4 shadow-lg transition-all data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=move]:transition-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full",
          description: "text-sm text-gray-600",
          actionButton:
            "inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none bg-[#067977] text-white hover:bg-[#067977]/90 h-9 px-3",
          cancelButton:
            "inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none bg-gray-100 text-gray-900 hover:bg-gray-200 h-9 px-3",
          closeButton:
            "absolute right-2 top-2 rounded-md p-1 text-gray-400 opacity-70 transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 group-hover:opacity-100",
          // Success toast - green accent
          success: "border-green-500 bg-green-50 text-green-900 [&>svg]:text-green-500",
          // Error toast - red accent  
          error: "border-red-500 bg-red-50 text-red-900 [&>svg]:text-red-500",
          // Warning toast - yellow accent
          warning: "border-yellow-500 bg-yellow-50 text-yellow-900 [&>svg]:text-yellow-500",
          // Info toast - blue accent
          info: "border-blue-500 bg-blue-50 text-blue-900 [&>svg]:text-blue-500",
        },
        // Accessibility: Allow users more time to read based on content length
        duration: 5000,
      }}
      {...props}
    />
  )
}

export { Toaster }
