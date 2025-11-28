import { apiClient } from '../api-client'
import { User, LoginCredentials, SignupData, ApiResponse } from '../types'

export const authService = {
  /**
   * Login user
   */
  async login(credentials: LoginCredentials): Promise<{ success: boolean; message: string; user?: User; data?: any }> {
    try {
      // Use FormData for PHP compatibility
      const formData = new FormData()
      formData.append('email', credentials.email)
      formData.append('password', credentials.password)

      const response = await apiClient.postFormData<ApiResponse<{ user: User }>>('/users/login.php',
        formData
      )

      return {
        success: true,
        message: response.message || 'Login successful',
        user: response.data?.user,
        data: response.data, // Include full data for nested access
      }
    } catch (error: any) {
      console.error('Login failed:', error)
      return {
        success: false,
        message: error.message || 'Login failed',
      }
    }
  },

  /**
   * Sign up new user
   */
  async signup(data: SignupData): Promise<{ success: boolean; message: string; user?: User }> {
    try {
      const formData = new FormData()
      Object.entries(data).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          formData.append(key, value.toString())
        }
      })

      const response = await apiClient.postFormData<ApiResponse<{ user: User }>>('/users/signup.php',
        formData
      )

      return {
        success: true,
        message: response.message || 'Account created successfully',
        user: response.data?.user,
      }
    } catch (error: any) {
      console.error('Signup failed:', error)
      return {
        success: false,
        message: error.message || 'Signup failed',
      }
    }
  },

  /**
   * Logout user
   */
  async logout(): Promise<{ success: boolean; message: string }> {
    try {
      await apiClient.post<ApiResponse<void>>('/logout.php', {})

      return {
        success: true,
        message: 'Logged out successfully',
      }
    } catch (error: any) {
      console.error('Logout failed:', error)
      return {
        success: false,
        message: error.message || 'Logout failed',
      }
    }
  },

  /**
   * Get current user
   */
  async getCurrentUser(): Promise<User | null> {
    try {
      const response = await apiClient.get<ApiResponse<User>>('/users/current.php')
      return response.data || null
    } catch (error) {
      console.error('Failed to fetch current user:', error)
      return null
    }
  },

  /**
   * Check if user is authenticated
   */
  async isAuthenticated(): Promise<boolean> {
    try {
      const user = await this.getCurrentUser()
      return user !== null
    } catch (error) {
      return false
    }
  },
}
