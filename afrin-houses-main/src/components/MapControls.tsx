import React from 'react';
import {
  ZoomIn,
  ZoomOut,
  Navigation,
  Layers,
  Fullscreen,
  Search,
  Filter,
  List,
  Map
} from 'lucide-react';
import { Button } from './ui/button';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from './ui/tooltip';

interface MapControlsProps {
  onZoomIn: () => void;
  onZoomOut: () => void;
  onResetView: () => void;
  onToggleFullscreen?: () => void;
  onToggleFilters?: () => void;
  onViewModeChange?: (mode: 'map' | 'list' | 'split') => void;
  viewMode?: 'map' | 'list' | 'split';
  showFilters?: boolean;
  isFullscreen?: boolean;
}

/**
 * Map Controls following Google Maps and Apple Maps patterns
 * Provides familiar map interaction controls
 */
const MapControls: React.FC<MapControlsProps> = ({
  onZoomIn,
  onZoomOut,
  onResetView,
  onToggleFullscreen,
  onToggleFilters,
  onViewModeChange,
  viewMode = 'map',
  showFilters = false,
  isFullscreen = false
}) => {
  return (
    <TooltipProvider>
      <div className="absolute top-4 right-4 z-[1000] flex flex-col gap-2">
        {/* View Mode Toggle - Following Google Maps pattern */}
        {onViewModeChange && (
          <div className="bg-white rounded-lg shadow-lg p-1 flex gap-1">
            <Tooltip>
              <TooltipTrigger asChild>
                <Button
                  variant={viewMode === 'map' ? 'default' : 'ghost'}
                  size="sm"
                  onClick={() => onViewModeChange('map')}
                  className="h-9 w-9 p-0"
                  aria-label="Map view"
                >
                  <Map className="h-4 w-4" />
                </Button>
              </TooltipTrigger>
              <TooltipContent side="left">Map View</TooltipContent>
            </Tooltip>

            <Tooltip>
              <TooltipTrigger asChild>
                <Button
                  variant={viewMode === 'split' ? 'default' : 'ghost'}
                  size="sm"
                  onClick={() => onViewModeChange('split')}
                  className="h-9 w-9 p-0"
                  aria-label="Split view"
                >
                  <Layers className="h-4 w-4" />
                </Button>
              </TooltipTrigger>
              <TooltipContent side="left">Split View</TooltipContent>
            </Tooltip>

            <Tooltip>
              <TooltipTrigger asChild>
                <Button
                  variant={viewMode === 'list' ? 'default' : 'ghost'}
                  size="sm"
                  onClick={() => onViewModeChange('list')}
                  className="h-9 w-9 p-0"
                  aria-label="List view"
                >
                  <List className="h-4 w-4" />
                </Button>
              </TooltipTrigger>
              <TooltipContent side="left">List View</TooltipContent>
            </Tooltip>
          </div>
        )}

        {/* Zoom Controls - Following standard map patterns */}
        <div className="bg-white rounded-lg shadow-lg p-1 flex flex-col gap-1">
          <Tooltip>
            <TooltipTrigger asChild>
              <Button
                variant="ghost"
                size="sm"
                onClick={onZoomIn}
                className="h-9 w-9 p-0 hover:bg-gray-100"
                aria-label="Zoom in"
              >
                <ZoomIn className="h-4 w-4" />
              </Button>
            </TooltipTrigger>
            <TooltipContent side="left">Zoom In</TooltipContent>
          </Tooltip>

          <div className="h-px bg-gray-200" />

          <Tooltip>
            <TooltipTrigger asChild>
              <Button
                variant="ghost"
                size="sm"
                onClick={onZoomOut}
                className="h-9 w-9 p-0 hover:bg-gray-100"
                aria-label="Zoom out"
              >
                <ZoomOut className="h-4 w-4" />
              </Button>
            </TooltipTrigger>
            <TooltipContent side="left">Zoom Out</TooltipContent>
          </Tooltip>
        </div>

        {/* Additional Controls */}
        <div className="bg-white rounded-lg shadow-lg p-1 flex flex-col gap-1">
          <Tooltip>
            <TooltipTrigger asChild>
              <Button
                variant="ghost"
                size="sm"
                onClick={onResetView}
                className="h-9 w-9 p-0 hover:bg-gray-100"
                aria-label="Reset view"
              >
                <Navigation className="h-4 w-4" />
              </Button>
            </TooltipTrigger>
            <TooltipContent side="left">Reset View</TooltipContent>
          </Tooltip>

          {onToggleFilters && (
            <>
              <div className="h-px bg-gray-200" />
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button
                    variant={showFilters ? 'default' : 'ghost'}
                    size="sm"
                    onClick={onToggleFilters}
                    className="h-9 w-9 p-0"
                    aria-label="Toggle filters"
                  >
                    <Filter className="h-4 w-4" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="left">
                  {showFilters ? 'Hide Filters' : 'Show Filters'}
                </TooltipContent>
              </Tooltip>
            </>
          )}

          {onToggleFullscreen && (
            <>
              <div className="h-px bg-gray-200" />
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={onToggleFullscreen}
                    className="h-9 w-9 p-0 hover:bg-gray-100"
                    aria-label="Toggle fullscreen"
                  >
                    <Fullscreen className="h-4 w-4" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent side="left">
                  {isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'}
                </TooltipContent>
              </Tooltip>
            </>
          )}
        </div>
      </div>
    </TooltipProvider>
  );
};

export default MapControls;