// API Client - handles all HTTP requests to PHP backend

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost'
const API_BASE = process.env.NEXT_PUBLIC_API_BASE || '/api'

export class ApiClient {
  private baseUrl: string

  constructor() {
    this.baseUrl = `${API_URL}${API_BASE}`
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    const url = `${this.baseUrl}${endpoint}`

    const config: RequestInit = {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      credentials: 'include', // Important for PHP sessions
    }

    try {
      const response = await fetch(url, config)

      // Check if response is JSON
      const contentType = response.headers.get('content-type')
      if (contentType && contentType.includes('application/json')) {
        const data = await response.json()

        if (!response.ok) {
          throw new Error(data.error || data.message || 'API request failed')
        }

        return data
      } else {
        // Handle non-JSON responses (like redirects from PHP)
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return {} as T
      }
    } catch (error) {
      console.error('API request error:', error)
      throw error
    }
  }

  async get<T>(endpoint: string): Promise<T> {
    return this.request<T>(endpoint, { method: 'GET' })
  }

  async post<T>(endpoint: string, data: any): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'POST',
      body: JSON.stringify(data),
    })
  }

  async postFormData<T>(endpoint: string, formData: FormData): Promise<T> {
    const url = `${this.baseUrl}${endpoint}`

    const config: RequestInit = {
      method: 'POST',
      body: formData,
      credentials: 'include',
      // Don't set Content-Type header - browser will set it with boundary
    }

    try {
      const response = await fetch(url, config)

      const contentType = response.headers.get('content-type')
      if (contentType && contentType.includes('application/json')) {
        const data = await response.json()

        if (!response.ok) {
          throw new Error(data.error || data.message || 'API request failed')
        }

        return data
      } else {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return {} as T
      }
    } catch (error) {
      console.error('API request error:', error)
      throw error
    }
  }

  async put<T>(endpoint: string, data: any): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data),
    })
  }

  async delete<T>(endpoint: string): Promise<T> {
    return this.request<T>(endpoint, { method: 'DELETE' })
  }
}

// Export singleton instance
export const apiClient = new ApiClient()
