import { apiClient } from '../api-client'
import { Property, ApiResponse } from '../types'

export const propertiesService = {
  /**
   * Get all available properties
   */
  async getAll(): Promise<Property[]> {
    try {
      const response = await apiClient.get<ApiResponse<Property[]>>('/properties/list.php')
      return response.data || []
    } catch (error) {
      console.error('Failed to fetch properties:', error)
      return []
    }
  },

  /**
   * Get a single property by ID
   */
  async getById(id: string): Promise<Property | null> {
    try {
      const response = await apiClient.get<ApiResponse<Property>>(`/properties/get.php?id=${id}`)
      return response.data || null
    } catch (error) {
      console.error('Failed to fetch property:', error)
      return null
    }
  },

  /**
   * Get featured properties
   */
  async getFeatured(limit: number = 4): Promise<Property[]> {
    try {
      const response = await apiClient.get<ApiResponse<Property[]>>(`/properties/featured.php?limit=${limit}`)
      return response.data || []
    } catch (error) {
      console.error('Failed to fetch featured properties:', error)
      return []
    }
  },

  /**
   * Search properties
   */
  async search(params: {
    location?: string
    minPrice?: number
    maxPrice?: number
    bedrooms?: number
  }): Promise<Property[]> {
    try {
      const queryParams = new URLSearchParams()
      if (params.location) queryParams.append('location', params.location)
      if (params.minPrice) queryParams.append('min_price', params.minPrice.toString())
      if (params.maxPrice) queryParams.append('max_price', params.maxPrice.toString())
      if (params.bedrooms) queryParams.append('bedrooms', params.bedrooms.toString())

      const response = await apiClient.get<ApiResponse<Property[]>>(
        `/properties/search.php?${queryParams.toString()}`
      )
      return response.data || []
    } catch (error) {
      console.error('Failed to search properties:', error)
      return []
    }
  },
}
