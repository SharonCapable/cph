'use client'

import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Upload, CheckCircle2 } from 'lucide-react'
import Navigation from '@/components/navigation'
import Footer from '@/components/footer'

export default function NewPropertyPage() {
  const [step, setStep] = useState(1)
  const [submitted, setSubmitted] = useState(false)
  const [formData, setFormData] = useState({
    title: '',
    location: '',
    bedrooms: '',
    bathrooms: '',
    price: '',
    description: '',
    amenities: [] as string[],
  })

  const amenities = ['WiFi', 'AC', 'Kitchen', 'Washer', 'Parking', 'Gym', 'Pool', 'Balcony']

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
  }

  const handleAmenityToggle = (amenity: string) => {
    setFormData(prev => ({
      ...prev,
      amenities: prev.amenities.includes(amenity)
        ? prev.amenities.filter(a => a !== amenity)
        : [...prev.amenities, amenity],
    }))
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    if (step < 3) {
      setStep(step + 1)
    } else {
      setSubmitted(true)
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
                <CheckCircle2 className="w-8 h-8 text-accent" />
              </div>
            </div>
            <h1 className="text-3xl font-bold text-foreground mb-4">Property Listed!</h1>
            <p className="text-muted-foreground mb-8">
              Your property has been added and is now visible to guests on our platform.
            </p>
            <Button className="w-full" onClick={() => window.location.href = '/manager/properties'}>
              Back to My Properties
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
          <div className="mb-12">
            <h1 className="text-4xl font-bold text-foreground mb-3">Add New Property</h1>
            <p className="text-lg text-muted-foreground">List your property in a few simple steps</p>
          </div>

          <div className="flex justify-between mb-12">
            {[1, 2, 3].map(s => (
              <div key={s} className="flex flex-col items-center flex-1">
                <div
                  className={`w-10 h-10 rounded-full flex items-center justify-center font-bold mb-2 transition ${
                    s <= step
                      ? 'bg-primary text-primary-foreground'
                      : 'bg-muted text-muted-foreground'
                  }`}
                >
                  {s}
                </div>
                <div className="text-sm font-medium text-foreground">
                  {s === 1 && 'Details'}
                  {s === 2 && 'Amenities'}
                  {s === 3 && 'Photos'}
                </div>
              </div>
            ))}
          </div>

          <form onSubmit={handleSubmit}>
            {step === 1 && (
              <Card className="p-8 bg-card border-border space-y-6">
                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">Property Title *</label>
                  <input
                    type="text"
                    name="title"
                    value={formData.title}
                    onChange={handleInputChange}
                    placeholder="e.g., Luxury 2-Bedroom Apartment"
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">Location *</label>
                  <input
                    type="text"
                    name="location"
                    value={formData.location}
                    onChange={handleInputChange}
                    placeholder="e.g., Accra, Ghana"
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                    required
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-foreground mb-2">Bedrooms *</label>
                    <select
                      name="bedrooms"
                      value={formData.bedrooms}
                      onChange={handleInputChange}
                      className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                      required
                    >
                      <option value="">Select...</option>
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4+</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-semibold text-foreground mb-2">Bathrooms *</label>
                    <select
                      name="bathrooms"
                      value={formData.bathrooms}
                      onChange={handleInputChange}
                      className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                      required
                    >
                      <option value="">Select...</option>
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4+</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">Price Per Night ($) *</label>
                  <input
                    type="number"
                    name="price"
                    value={formData.price}
                    onChange={handleInputChange}
                    placeholder="e.g., 75"
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-semibold text-foreground mb-2">Description *</label>
                  <textarea
                    name="description"
                    value={formData.description}
                    onChange={handleInputChange}
                    placeholder="Describe your property..."
                    rows={4}
                    className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary resize-none"
                    required
                  />
                </div>

                <Button type="submit" className="w-full">
                  Next
                </Button>
              </Card>
            )}

            {step === 2 && (
              <Card className="p-8 bg-card border-border space-y-6">
                <h3 className="text-lg font-semibold text-foreground">Select Amenities</h3>
                <div className="grid grid-cols-2 gap-4">
                  {amenities.map(amenity => (
                    <label key={amenity} className="flex items-center gap-3 p-3 border border-border rounded-lg cursor-pointer hover:bg-primary/5">
                      <input
                        type="checkbox"
                        checked={formData.amenities.includes(amenity)}
                        onChange={() => handleAmenityToggle(amenity)}
                        className="w-5 h-5 rounded accent-primary"
                      />
                      <span className="text-foreground">{amenity}</span>
                    </label>
                  ))}
                </div>

                <div className="flex gap-4">
                  <Button
                    type="button"
                    variant="outline"
                    className="flex-1"
                    onClick={() => setStep(1)}
                  >
                    Back
                  </Button>
                  <Button
                    type="submit"
                    className="flex-1"
                  >
                    Next
                  </Button>
                </div>
              </Card>
            )}

            {step === 3 && (
              <Card className="p-8 bg-card border-border space-y-6">
                <div className="border-2 border-dashed border-border rounded-lg p-8 text-center">
                  <Upload className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-foreground font-semibold mb-2">Upload Property Photos</p>
                  <p className="text-sm text-muted-foreground mb-4">Drag and drop your images here or click to browse</p>
                  <input
                    type="file"
                    multiple
                    accept="image/*"
                    className="hidden"
                  />
                  <Button variant="outline">Choose Files</Button>
                </div>

                <div className="flex gap-4">
                  <Button
                    type="button"
                    variant="outline"
                    className="flex-1"
                    onClick={() => setStep(2)}
                  >
                    Back
                  </Button>
                  <Button
                    type="submit"
                    className="flex-1 bg-accent hover:bg-accent/90"
                  >
                    List Property
                  </Button>
                </div>
              </Card>
            )}
          </form>
        </div>
      </main>

      <Footer />
    </div>
  )
}
