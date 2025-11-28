'use client'

import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { AlertCircle, CheckCircle2 } from 'lucide-react'
import Navigation from '@/components/navigation'
import Footer from '@/components/footer'

export default function ListPropertyPage() {
  const [formStep, setFormStep] = useState(1)
  const [submitted, setSubmitted] = useState(false)
  const [isApproved, setIsApproved] = useState(false)
  const [formData, setFormData] = useState({
    propertyName: '',
    location: '',
    bedrooms: '',
    bathrooms: '',
    pricePerNight: '',
    description: '',
    amenities: [] as string[],
    contactName: '',
    contactEmail: '',
    contactPhone: '',
  })

  const amenitiesOptions = [
    'WiFi',
    'Air Conditioning',
    'Kitchen',
    'Washer/Dryer',
    'Parking',
    'Gym',
    'Pool',
    'Balcony',
  ]

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setFormData((prev) => ({ ...prev, [name]: value }))
  }

  const handleAmenityToggle = (amenity: string) => {
    setFormData((prev) => ({
      ...prev,
      amenities: prev.amenities.includes(amenity)
        ? prev.amenities.filter((a) => a !== amenity)
        : [...prev.amenities, amenity],
    }))
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (formStep === 1) {
      setFormStep(2)
    } else if (formStep === 2) {
      setFormStep(3)
    } else {
      // Submit to API
      try {
        const { applicationsService } = await import('@/lib/services')
        const result = await applicationsService.submit({
          full_name: formData.contactName,
          email: formData.contactEmail,
          phone: formData.contactPhone,
          properties_count: parseInt(formData.bedrooms) || 0,
          experience_years: parseInt(formData.bathrooms) || 0,
          message: formData.description,
        })

        if (result.success) {
          setSubmitted(true)
        } else {
          alert('Failed to submit application. Please try again.')
        }
      } catch (error) {
        console.error('Submission error:', error)
        alert('Failed to submit application. Please try again.')
      }
    }
  }

  if (submitted) {
    return (
      <div className="min-h-screen flex flex-col bg-background">
        <Navigation />
        <div className="flex-1 flex items-center justify-center py-12 px-4">
          <Card className="p-12 text-center max-w-md bg-card border-border">
            <div className="flex justify-center mb-6">
              <div className="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
                <AlertCircle className="w-8 h-8 text-accent" />
              </div>
            </div>
            <h1 className="text-3xl font-bold text-foreground mb-4">Application Pending Review</h1>
            <p className="text-muted-foreground mb-2">
              Thank you for submitting your application. Our super admin team will review your property details.
            </p>
            <p className="text-muted-foreground mb-8">
              Once approved, you'll receive an email and can start uploading your properties to our platform.
            </p>
            <Button className="w-full" onClick={() => window.location.href = '/'}>
              Back to Home
            </Button>
          </Card>
        </div>
        <Footer />
      </div>
    )
  }

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Navigation />

      <main className="flex-1 py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-2xl mx-auto">
          {/* Header */}
          <div className="mb-12">
            <h1 className="text-4xl sm:text-5xl font-bold text-foreground mb-3">List Your Property</h1>
            <p className="text-lg text-muted-foreground">
              Join our network of property managers. Submit your application for approval, and once approved, you can list multiple properties.
            </p>
          </div>

          {/* Progress Steps */}
          <div className="flex justify-between mb-12">
            {[1, 2, 3].map((step) => (
              <div key={step} className="flex flex-col items-center flex-1">
                <div
                  className={`w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2 transition ${
                    step <= formStep
                      ? 'bg-primary text-primary-foreground'
                      : 'bg-muted text-muted-foreground'
                  }`}
                >
                  {step}
                </div>
                <div className="text-sm font-medium text-foreground">
                  {step === 1 && 'About You'}
                  {step === 2 && 'Experience'}
                  {step === 3 && 'Contact'}
                </div>
                {step < 3 && (
                  <div
                    className={`hidden sm:block h-1 w-full mt-2 ${
                      step < formStep ? 'bg-primary' : 'bg-muted'
                    }`}
                  />
                )}
              </div>
            ))}
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit}>
            {/* Step 1: Property Manager Info */}
            {formStep === 1 && (
              <Card className="p-8 bg-card border-border space-y-6">
                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">Full Name *</label>
                  <input
                    type="text"
                    name="contactName"
                    value={formData.contactName}
                    onChange={handleInputChange}
                    placeholder="Your full name"
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">Email Address *</label>
                  <input
                    type="email"
                    name="contactEmail"
                    value={formData.contactEmail}
                    onChange={handleInputChange}
                    placeholder="your@email.com"
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">Phone Number *</label>
                  <input
                    type="tel"
                    name="contactPhone"
                    value={formData.contactPhone}
                    onChange={handleInputChange}
                    placeholder="+233 123 456 789"
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                    required
                  />
                </div>

                <Button type="submit" className="w-full bg-primary hover:bg-primary/90 text-primary-foreground font-semibold">
                  Next: Tell Us More
                </Button>
              </Card>
            )}

            {/* Step 2: Property Experience */}
            {formStep === 2 && (
              <Card className="p-8 bg-card border-border space-y-6">
                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">How many properties do you currently manage? *</label>
                  <select
                    name="bedrooms"
                    value={formData.bedrooms}
                    onChange={handleInputChange}
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                    required
                  >
                    <option value="">Select...</option>
                    <option value="0">This will be my first</option>
                    <option value="1">1 property</option>
                    <option value="2">2-5 properties</option>
                    <option value="3">6-10 properties</option>
                    <option value="4">10+ properties</option>
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">How many years of property management experience do you have? *</label>
                  <select
                    name="bathrooms"
                    value={formData.bathrooms}
                    onChange={handleInputChange}
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                    required
                  >
                    <option value="">Select...</option>
                    <option value="1">Less than 1 year</option>
                    <option value="2">1-3 years</option>
                    <option value="3">3-5 years</option>
                    <option value="4">5+ years</option>
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">Tell us about your properties *</label>
                  <textarea
                    name="description"
                    value={formData.description}
                    onChange={handleInputChange}
                    placeholder="Describe the types of properties you manage and what makes them special..."
                    rows={5}
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary resize-none"
                    required
                  />
                </div>

                <div className="flex gap-4">
                  <Button
                    type="button"
                    variant="outline"
                    className="flex-1"
                    onClick={() => setFormStep(1)}
                  >
                    Back
                  </Button>
                  <Button
                    type="submit"
                    className="flex-1 bg-primary hover:bg-primary/90 text-primary-foreground font-semibold"
                  >
                    Next: Confirm
                  </Button>
                </div>
              </Card>
            )}

            {/* Step 3: Confirmation */}
            {formStep === 3 && (
              <Card className="p-8 bg-card border-border space-y-6">
                <div className="bg-primary/10 border border-primary/30 rounded-lg p-4">
                  <p className="text-sm text-foreground">
                    By submitting this application, you agree to our terms of service and confirm that all information provided is accurate.
                  </p>
                </div>

                <div className="bg-muted/50 rounded-lg p-6 space-y-4 text-sm">
                  <div><span className="font-semibold">Name:</span> {formData.contactName}</div>
                  <div><span className="font-semibold">Email:</span> {formData.contactEmail}</div>
                  <div><span className="font-semibold">Phone:</span> {formData.contactPhone}</div>
                </div>

                <div className="flex gap-4">
                  <Button
                    type="button"
                    variant="outline"
                    className="flex-1"
                    onClick={() => setFormStep(2)}
                  >
                    Back
                  </Button>
                  <Button
                    type="submit"
                    className="flex-1 bg-accent hover:bg-accent/90 text-accent-foreground font-semibold"
                  >
                    Submit Application
                  </Button>
                </div>
              </Card>
            )}
          </form>

          {/* Benefits */}
          <div className="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-6">
            {[
              { title: 'Quick Process', desc: 'Apply in minutes' },
              { title: 'Fast Review', desc: 'Decision within 24 hours' },
              { title: 'Start Listing', desc: 'Upload properties after approval' },
            ].map((benefit, index) => (
              <Card key={index} className="p-4 text-center bg-primary/5 border-border">
                <h4 className="font-semibold text-foreground mb-2">{benefit.title}</h4>
                <p className="text-sm text-muted-foreground">{benefit.desc}</p>
              </Card>
            ))}
          </div>
        </div>
      </main>

      <Footer />
    </div>
  )
}
