'use client'

import { useState, useEffect } from 'react'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { MapPin, Bed, Bath, Wifi, Wind, Droplets, Utensils, ChevronLeft, ChevronRight, MessageCircle } from 'lucide-react'
import Link from 'next/link'
import Navigation from '@/components/navigation'
import Footer from '@/components/footer'
import BookingDialog from '@/components/booking-dialog'
import { propertiesService } from '@/lib/services'
import { Property } from '@/lib/types'
import { useAuth } from '@/lib/auth-context'
import { getImageUrl } from '@/lib/utils'

// Default amenities
const defaultAmenities = [
  { icon: Wifi, name: 'High-Speed WiFi' },
  { icon: Wind, name: 'Air Conditioning' },
  { icon: Droplets, name: 'Hot Water' },
  { icon: Utensils, name: 'Fully Equipped Kitchen' },
]

// WhatsApp number from env
const WHATSAPP_NUMBER = '+233207119731'

export default function PropertyDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { user } = useAuth()
  const [property, setProperty] = useState<Property | null>(null)
  const [loading, setLoading] = useState(true)
  const [imageIndex, setImageIndex] = useState(0)
  const [propertyId, setPropertyId] = useState<string | null>(null)
  const [showBookingDialog, setShowBookingDialog] = useState(false)

  useEffect(() => {
    params.then(p => setPropertyId(p.id))
  }, [params])

  useEffect(() => {
    async function loadProperty() {
      if (!propertyId) return

      try {
        setLoading(true)
        const data = await propertiesService.getById(propertyId)
        setProperty(data)
      } catch (error) {
        console.error('Failed to load property:', error)
      } finally {
        setLoading(false)
      }
    }

    loadProperty()
  }, [propertyId])

  if (loading) {
    return (
      <div className="min-h-screen flex flex-col bg-background">
        <Navigation />
        <div className="flex-1 flex items-center justify-center">
          <p className="text-muted-foreground">Loading property...</p>
        </div>
        <Footer />
      </div>
    )
  }

  if (!property) {
    return (
      <div className="min-h-screen flex flex-col bg-background">
        <Navigation />
        <div className="flex-1 flex items-center justify-center">
          <Card className="p-8 text-center bg-card border-border">
            <h1 className="text-2xl font-bold text-foreground mb-2">Property Not Found</h1>
            <p className="text-muted-foreground mb-6">The property you're looking for doesn't exist.</p>
            <Link href="/properties">
              <Button>Back to Properties</Button>
            </Link>
          </Card>
        </div>
        <Footer />
      </div>
    )
  }

  const nextImage = () => {
    if (property.images && property.images.length > 0) {
      setImageIndex((prev) => (prev + 1) % property.images.length)
    }
  }

  const prevImage = () => {
    if (property.images && property.images.length > 0) {
      setImageIndex((prev) => (prev - 1 + property.images.length) % property.images.length)
    }
  }

  const propertyImages = property.images || []
  const currentImageUrl = getImageUrl(propertyImages[imageIndex]?.image_path)

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Navigation />

      <main className="flex-1 py-8 px-4 sm:px-6 lg:px-8">
        <div className="max-w-6xl mx-auto">
          {/* Back Button */}
          <Link href="/properties" className="inline-flex items-center gap-2 text-primary hover:text-primary/80 mb-6">
            <ChevronLeft className="w-4 h-4" />
            Back to Properties
          </Link>

          {/* Image Gallery */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div className="lg:col-span-2">
              <div className="relative rounded-2xl overflow-hidden h-96 lg:h-96 bg-muted">
                <img
                  src={currentImageUrl}
                  alt={property.title}
                  className="w-full h-full object-cover"
                />
                {propertyImages.length > 1 && (
                  <>
                    <button
                      onClick={prevImage}
                      className="absolute left-4 top-1/2 -translate-y-1/2 bg-background/80 hover:bg-background rounded-full p-2 transition"
                      aria-label="Previous image"
                    >
                      <ChevronLeft className="w-6 h-6" />
                    </button>
                    <button
                      onClick={nextImage}
                      className="absolute right-4 top-1/2 -translate-y-1/2 bg-background/80 hover:bg-background rounded-full p-2 transition"
                      aria-label="Next image"
                    >
                      <ChevronRight className="w-6 h-6" />
                    </button>
                  </>
                )}
              </div>
              {propertyImages.length > 1 && (
                <div className="flex gap-2 mt-4 overflow-x-auto">
                  {propertyImages.map((img, index) => (
                    <button
                      key={img.id}
                      onClick={() => setImageIndex(index)}
                      className={`h-20 min-w-20 rounded-lg overflow-hidden border-2 transition ${index === imageIndex ? 'border-primary' : 'border-border'
                        }`}
                    >
                      <img src={getImageUrl(img.image_path)} alt={`View ${index + 1}`} className="w-full h-full object-cover" />
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Quick Info Card */}
            <div>
              <Card className="p-6 bg-card border-border sticky top-24">
                <div className="space-y-6">
                  <div>
                    <div className="text-3xl font-bold text-primary mb-1">
                      ${property.price_per_month}
                      <span className="text-lg text-muted-foreground font-normal">/night</span>
                    </div>
                  </div>

                  <div className="space-y-3">
                    <div className="flex items-center gap-3">
                      <MapPin className="w-5 h-5 text-primary flex-shrink-0" />
                      <span className="text-foreground">{property.location}</span>
                    </div>
                    <div className="flex gap-6">
                      <div className="flex items-center gap-2">
                        <Bed className="w-5 h-5 text-primary" />
                        <span className="text-foreground">{property.bedrooms} Bed</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <Bath className="w-5 h-5 text-primary" />
                        <span className="text-foreground">{property.bathrooms} Bath</span>
                      </div>
                    </div>
                  </div>

                  <Button
                    onClick={() => user ? setShowBookingDialog(true) : window.location.href = '/login'}
                    className="w-full h-12 bg-accent hover:bg-accent/90 text-accent-foreground font-semibold"
                  >
                    {user ? 'Book Now' : 'Sign In to Book'}
                  </Button>
                  <Button
                    variant="outline"
                    className="w-full"
                    onClick={() => window.open(`https://wa.me/${WHATSAPP_NUMBER.replace(/[^0-9]/g, '')}?text=Hi, I'm interested in the property: ${property.title}`, '_blank')}
                  >
                    <MessageCircle className="w-4 h-4 mr-2" />
                    Ask Owner on WhatsApp
                  </Button>
                </div>
              </Card>
            </div>
          </div>

          {/* Property Details */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2 space-y-8">
              {/* Description */}
              <Card className="p-6 bg-card border-border">
                <h2 className="text-2xl font-bold text-foreground mb-4">{property.title}</h2>
                <p className="text-muted-foreground leading-relaxed whitespace-pre-wrap">{property.description}</p>
              </Card>

              {/* Amenities */}
              <Card className="p-6 bg-card border-border">
                <h3 className="text-xl font-bold text-foreground mb-6">Amenities</h3>
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                  {defaultAmenities.map((amenity, index) => {
                    const Icon = amenity.icon
                    return (
                      <div key={index} className="flex flex-col items-center gap-2 text-center">
                        <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                          <Icon className="w-6 h-6 text-primary" />
                        </div>
                        <span className="text-sm font-medium text-foreground">{amenity.name}</span>
                      </div>
                    )
                  })}
                </div>
              </Card>

              {/* Property Info */}
              <Card className="p-6 bg-card border-border">
                <h3 className="text-xl font-bold text-foreground mb-6">Property Information</h3>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Type</p>
                    <p className="font-medium text-foreground">{property.property_type || 'Apartment'}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Furnishing</p>
                    <p className="font-medium text-foreground">{property.furnishing_status || 'Furnished'}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Available From</p>
                    <p className="font-medium text-foreground">
                      {property.available_from ? new Date(property.available_from).toLocaleDateString() : 'Now'}
                    </p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground mb-1">Status</p>
                    <p className="font-medium text-foreground capitalize">{property.status}</p>
                  </div>
                </div>
              </Card>
            </div>
          </div>
        </div>
      </main>

      <Footer />

      {/* Booking Dialog */}
      {showBookingDialog && property && (
        <BookingDialog
          propertyId={property.id}
          propertyTitle={property.title}
          pricePerNight={property.price_per_month}
          onClose={() => setShowBookingDialog(false)}
        />
      )}
    </div>
  )
}
