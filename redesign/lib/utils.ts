import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function getImageUrl(path: string | null | undefined): string {
  if (!path) return '/placeholder.svg'
  
  // If path is already a full URL, return it
  if (path.startsWith('http')) return path
  
  const uploadUrl = process.env.NEXT_PUBLIC_UPLOAD_URL || ''
  
  // Clean up path - remove leading slash if present
  const cleanPath = path.startsWith('/') ? path.substring(1) : path
  
  // Check if path already starts with uploads/ and base URL ends with uploads
  // This handles the case where DB has /uploads/path and env is .../uploads
  if (uploadUrl.endsWith('/uploads') && cleanPath.startsWith('uploads/')) {
    return `${uploadUrl.replace(/\/uploads$/, '')}/${cleanPath}`
  }
  
  return `${uploadUrl}/${cleanPath}`
}
