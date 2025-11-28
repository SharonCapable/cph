import { apiClient } from '../api-client'
import { Application, ApiResponse } from '../types'

export const applicationsService = {
  /**
   * Submit a property manager application
   */
  async submit(applicationData: {
    full_name: string
    email: string
    phone: string
    company_name?: string
    properties_count: number
    experience_years: number
    message: string
  }): Promise<{ success: boolean; message: string }> {
    try {
      const formData = new FormData()

      Object.entries(applicationData).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          formData.append(key, value.toString())
        }
      })

      const response = await apiClient.postFormData<ApiResponse<void>>(
        '/applications/submit.php',
        formData
      )

      return {
        success: true,
        message: response.message || 'Application submitted successfully',
      }
    } catch (error: any) {
      console.error('Failed to submit application:', error)
      return {
        success: false,
        message: error.message || 'Failed to submit application',
      }
    }
  },

  /**
   * Get application status by email
   */
  async getStatus(email: string): Promise<Application | null> {
    try {
      const response = await apiClient.get<ApiResponse<Application>>(
        `/applications/status.php?email=${encodeURIComponent(email)}`
      )
      return response.data || null
    } catch (error) {
      console.error('Failed to fetch application status:', error)
      return null
    }
  },
}
