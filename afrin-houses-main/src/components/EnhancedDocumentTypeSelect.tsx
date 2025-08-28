import React, { useState, useMemo, memo } from 'react';
import {
  File,
  FileText,
  RefreshCw,
  Leaf,
  Building,
  Clock,
  Users,
  Loader2,
  AlertCircle
} from 'lucide-react';
import {
  Select,
  SelectContent,
  SelectTrigger,
  SelectValue,
} from './ui/select';
import * as SelectPrimitive from "@radix-ui/react-select";

import { cn } from '../lib/utils';
import { PropertyDocumentType } from '../services/propertyDocumentTypeService';

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

// Icon mapping for different document types
const getDocumentTypeIcon = (id: number) => {
  const iconMap: Record<number, { icon: React.ComponentType<any>; color: string }> = {
    1: { icon: FileText, color: '#067977' }, // Regular Title
    2: { icon: RefreshCw, color: '#10b981' }, // Updated Title
    3: { icon: Leaf, color: '#22c55e' }, // Agricultural Title
    4: { icon: Building, color: '#f59e0b' }, // Construction Land
    5: { icon: Clock, color: '#ef4444' }, // Temporary Title
    6: { icon: Users, color: '#8b5cf6' }, // Family Title
  };

  return iconMap[id] || { icon: File, color: '#6b7280' };
};

const EnhancedDocumentTypeSelect: React.FC<EnhancedDocumentTypeSelectProps> = memo(({
  value,
  onValueChange,
  placeholder = "اختر نوع الطابو",
  disabled = false,
  loading = false,
  error = null,
  documentTypes = [],
  className,
  showDescriptions = true,
  maxHeight = 320,
}) => {
  const [isOpen, setIsOpen] = useState(false);

  // Handle value change
  const handleValueChange = (newValue: string) => {
    onValueChange(newValue);
    setIsOpen(false);
  };

  // Memoize selected type to prevent unnecessary recalculations
  const selectedType = useMemo(() => {
    if (value && documentTypes.length > 0) {
      return documentTypes.find(type => type.id.toString() === value) || null;
    }
    return null;
  }, [value, documentTypes]);

  // Memoize sorted document types
  const sortedDocumentTypes = useMemo(() => {
    return [...documentTypes].sort((a, b) => a.sort_order - b.sort_order);
  }, [documentTypes]);

  return (
    <div className={cn("relative", className)}>
      <Select
        value={value}
        onValueChange={handleValueChange}
        disabled={disabled || loading}
        open={isOpen}
        onOpenChange={setIsOpen}
        dir="rtl" // Right-to-left support for Arabic
      >
        <SelectTrigger
          className={cn(
            "h-12 text-base border-2 rounded-xl bg-white",
            "border-gray-200 hover:border-purple-300 focus:border-purple-500",
            "focus:ring-2 focus:ring-purple-100",
            disabled && "opacity-50 cursor-not-allowed",
            error && "border-red-500 focus:border-red-500 focus:ring-red-100",
            loading && "cursor-wait"
          )}
        >
          <div className="flex items-center gap-3 w-full">
            {/* Loading Icon */}
            {loading && (
              <div className="flex-shrink-0">
                <Loader2 className="h-5 w-5 animate-spin text-purple-500" />
              </div>
            )}

            {/* Selected Value or Placeholder */}
            <div className="flex-1 text-right">
              <SelectValue placeholder={placeholder} />
            </div>
          </div>
        </SelectTrigger>

        <SelectContent
          className="bg-white border border-gray-200 rounded-lg shadow-lg"
          style={{ maxHeight: `${maxHeight}px` }}
        >
          {/* Loading State */}
          {loading && (
            <div className="p-4 text-center">
              <Loader2 className="h-6 w-6 animate-spin mx-auto mb-2 text-purple-500" />
              <p className="text-sm text-gray-600">جاري تحميل أنواع التابو...</p>
            </div>
          )}

          {/* Error State */}
          {error && !loading && (
            <div className="p-4 text-center" id="document-type-error" role="alert">
              <AlertCircle className="h-6 w-6 mx-auto mb-2 text-red-500" />
              <p className="text-sm text-red-600 mb-2">{error}</p>
              <button
                onClick={() => window.location.reload()}
                className="text-xs text-purple-600 hover:text-purple-700 underline focus:outline-none focus:ring-2 focus:ring-purple-200 rounded"
                aria-label="إعادة تحميل أنواع التابو"
              >
                إعادة المحاولة
              </button>
            </div>
          )}

          {/* Document Type Options */}
          {!loading && !error && sortedDocumentTypes.map((docType) => {
            const { icon: IconComponent, color } = getDocumentTypeIcon(docType.id);
            const isSelected = value === docType.id.toString();

            return (
              <SelectPrimitive.Item
                key={docType.id}
                value={docType.id.toString()}
                className={cn(
                  "relative flex w-full cursor-default select-none items-center rounded-sm p-3 sm:p-4 min-h-[56px] text-sm outline-none",
                  "hover:bg-purple-50 focus:bg-purple-100 data-[disabled]:pointer-events-none data-[disabled]:opacity-50",
                  isSelected && "bg-purple-100 border-r-4 border-purple-500"
                )}
              >
                <SelectPrimitive.ItemText asChild>
                  <div className="flex items-center gap-3 w-full text-right">
                    <IconComponent
                      className="h-5 w-5 flex-shrink-0"
                      style={{ color }}
                    />
                    <div className="flex-1">
                      <div className="font-semibold text-gray-900 text-sm sm:text-base">
                        {docType.name}
                      </div>
                      {showDescriptions && docType.description && (
                        <div className="text-xs sm:text-sm text-gray-600 mt-1">
                          {docType.description}
                        </div>
                      )}
                    </div>
                  </div>
                </SelectPrimitive.ItemText>
              </SelectPrimitive.Item>
            );
          })}

          {/* Empty State */}
          {!loading && !error && sortedDocumentTypes.length === 0 && (
            <div className="p-4 text-center text-gray-500">
              <File className="h-6 w-6 mx-auto mb-2 text-gray-400" />
              <p className="text-sm">لا توجد أنواع تابو متاحة</p>
            </div>
          )}
        </SelectContent>
      </Select>
    </div>
  );
});

EnhancedDocumentTypeSelect.displayName = 'EnhancedDocumentTypeSelect';

export default EnhancedDocumentTypeSelect;