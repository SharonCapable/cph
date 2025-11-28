import { apiClient } from '../api-client'
import { Booking, BookingFormData, ApiResponse } from '../types'

export const bookingsService = {
  /**
   * Create a new booking
   */
  async create(bookingData: BookingFormData): Promise<{ success: boolean; message: string; bookingId?: string }> {
    try {
      // Convert to FormData for PHP compatibility
      const formData = new FormData()

      Object.entries(bookingData).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          formData.append(key, value.toString())
        }
      })

      const response = await apiClient.postFormData<ApiResponse<{ booking_id: string }>>(
        '/booking.php',
        formData
      )

      return {
        success: true,
        message: response.message || 'Booking created successfully',
        bookingId: response.data?.booking_id,
      }
    } catch (error: any) {
      console.error('Failed to create booking:', error)
      return {
        success: false,
        message: error.message || 'Failed to create booking',
      }
    }
  },

  /**
   * Get user's bookings
   */
  async getUserBookings(): Promise<Booking[]> {
    try {
      const response = await apiClient.get<ApiResponse<Booking[]>>('/bookings/list.php')
      return response.data || []
    } catch (error) {
      console.error('Failed to fetch bookings:', error)
      return []
    }
  },

  /**
   * Get a single booking by ID
   */
  async getById(id: string): Promise<Booking | null> {
    try {
      const response = await apiClient.get<ApiResponse<Booking>>(`/bookings/get.php?id=${id}`)
      return response.data || null
    } catch (error) {
      console.error('Failed to fetch booking:', error)
      return null
    }
  },

  /**
   * Cancel a booking
   */
  async cancel(bookingId: string): Promise<{ success: boolean; message: string }> {
    try {
      const response = await apiClient.post<ApiResponse<void>>('/bookings/cancel.php', {
        booking_id: bookingId,
      })

      return {
        success: true,
        message: response.message || 'Booking cancelled successfully',
      }
    } catch (error: any) {
      console.error('Failed to cancel booking:', error)
      return {
        success: false,
        message: error.message || 'Failed to cancel booking',
      }
    }
  },
}
