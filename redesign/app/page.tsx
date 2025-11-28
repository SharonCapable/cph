'use client'

import { useState, useEffect } from 'react'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { ArrowRight, MapPin, DollarSign, Bed, Bath, CheckCircle2, Clock, Lock } from 'lucide-react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import Navigation from '@/components/navigation'
import Footer from '@/components/footer'
import { propertiesService } from '@/lib/services'
import { getImageUrl } from '@/lib/utils'
import { Property } from '@/lib/types'

const benefits = [
  {
    icon: CheckCircle2,
    title: 'Verified Properties',
    description: 'All properties are verified by our team',
  },
  {
    icon: Clock,
    title: '24/7 Support',
    description: "We're here to help anytime you need",
  },
  {
    icon: Lock,
    title: 'Easy Booking',
    description: 'Simple and secure booking process',
  },
]

export default function Home() {
  const router = useRouter()
  const [featuredProperties, setFeaturedProperties] = useState<Property[]>([])
  const [loading, setLoading] = useState(true)
  const [searchLocation, setSearchLocation] = useState('')
  const [searchPrice, setSearchPrice] = useState('all')
  const [searchBedrooms, setSearchBedrooms] = useState('all')

  useEffect(() => {
    async function loadFeaturedProperties() {
      try {
        setLoading(true)
        const data = await propertiesService.getFeatured(4)
        setFeaturedProperties(data)
      } catch (error) {
        console.error('Failed to load featured properties:', error)
      } finally {
        setLoading(false)
      }
    }

    loadFeaturedProperties()
  }, [])

  const handleSearch = () => {
    const params = new URLSearchParams()
    if (searchLocation) params.append('location', searchLocation)
    if (searchPrice !== 'all') params.append('price', searchPrice)
    if (searchBedrooms !== 'all') params.append('bedrooms', searchBedrooms)

    router.push(`/properties?${params.toString()}`)
  }

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Navigation />

      {/* Hero Section */}
      <section className="relative w-full overflow-hidden pt-20 pb-32">
        <div className="absolute inset-0 bg-gradient-to-br from-primary/10 via-background to-background pointer-events-none" />

        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {/* Hero Content */}
            <div className="space-y-8">
              <div className="space-y-4">
                <h1 className="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-foreground">
                  Find Your
                  <span className="block text-primary">Perfect Home</span>
                </h1>
                <p className="text-lg sm:text-xl text-muted-foreground max-w-lg leading-relaxed">
                  Discover quality properties in premium locations. From short-term rentals to long-term stays, we connect you with homes that matter.
                </p>
              </div>

              {/* Search Bar */}
              <div className="bg-card border border-border rounded-2xl p-6 shadow-lg">
                <div className="space-y-4">
                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div className="flex items-center gap-2 px-4 py-3 bg-background rounded-lg border border-input">
                      <MapPin className="w-5 h-5 text-primary flex-shrink-0" />
                      <input
                        type="text"
                        placeholder="Location"
                        value={searchLocation}
                        onChange={(e) => setSearchLocation(e.target.value)}
                        className="flex-1 bg-transparent outline-none text-sm"
                      />
                    </div>
                    <div className="flex items-center gap-2 px-4 py-3 bg-background rounded-lg border border-input">
                      <DollarSign className="w-5 h-5 text-primary flex-shrink-0" />
                      <select
                        value={searchPrice}
                        onChange={(e) => setSearchPrice(e.target.value)}
                        className="flex-1 bg-transparent outline-none text-sm appearance-none"
                      >
                        <option value="all">Any price</option>
                        <option value="under50">Under $50</option>
                        <option value="50to100">$50 - $100</option>
                        <option value="over100">$100+</option>
                      </select>
                    </div>
                    <div className="flex items-center gap-2 px-4 py-3 bg-background rounded-lg border border-input">
                      <Bed className="w-5 h-5 text-primary flex-shrink-0" />
                      <select
                        value={searchBedrooms}
                        onChange={(e) => setSearchBedrooms(e.target.value)}
                        className="flex-1 bg-transparent outline-none text-sm appearance-none"
                      >
                        <option value="all">Any beds</option>
                        <option value="0">Studio</option>
                        <option value="1">1 Bed</option>
                        <option value="2">2+ Beds</option>
                      </select>
                    </div>
                  </div>
                  <Button
                    onClick={handleSearch}
                    className="w-full h-12 bg-accent hover:bg-accent/90 text-accent-foreground font-semibold"
                  >
                    Search Properties
                  </Button>
                </div>
              </div>

              {/* CTA Buttons */}
              <div className="flex flex-col sm:flex-row gap-4">
                <Link href="/properties">
                  <Button variant="default" size="lg" className="w-full">
                    Browse Properties
                    <ArrowRight className="w-4 h-4 ml-2" />
                  </Button>
                </Link>
                <Link href="/list-property">
                  <Button variant="outline" size="lg" className="w-full">
                    List Your Property
                  </Button>
                </Link>
              </div>
            </div>

            {/* Hero Image */}
            <div className="hidden lg:flex justify-center">
              <div className="relative w-full aspect-square max-w-md">
                <img
                  src="/luxury-apartment-exterior-modern.jpg"
                  alt="Beautiful apartment"
                  className="w-full h-full object-cover rounded-2xl shadow-2xl"
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Featured Properties Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8 bg-muted/30">
        <div className="max-w-7xl mx-auto">
          <div className="mb-12">
            <h2 className="text-4xl sm:text-5xl font-bold text-foreground mb-4">Featured Properties</h2>
            <p className="text-lg text-muted-foreground max-w-2xl">
              Discover our handpicked collection of premium apartments and homes available for short-term and long-term rentals.
            </p>
          </div>

          {featuredProperties.length > 0 ? (
            <>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {featuredProperties.map((property) => {
                  const primaryImage = property.images?.find((img: any) => img.is_primary) || property.images?.[0]
                  const imageUrl = getImageUrl(primaryImage?.image_path)

                  return (
                    <Link key={property.id} href={`/properties/${property.id}`}>
                      <Card className="group overflow-hidden hover:shadow-xl transition-shadow duration-300 h-full flex flex-col cursor-pointer bg-card border-border">
                        <div className="relative overflow-hidden h-48 bg-muted">
                          <img
                            src={imageUrl}
                            alt={property.title}
                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                          />
                        </div>
                        <div className="flex-1 p-5 flex flex-col justify-between">
                          <div className="space-y-3">
                            <h3 className="font-semibold text-foreground line-clamp-2 text-sm">
                              {property.title}
                            </h3>
                            <div className="flex items-center gap-2 text-muted-foreground text-sm">
                              <MapPin className="w-4 h-4" />
                              <span>{property.location}</span>
                            </div>
                          </div>
                          <div className="space-y-3 pt-3 border-t border-border">
                            <div className="flex gap-4 text-sm text-muted-foreground">
                              <div className="flex items-center gap-1">
                                <Bed className="w-4 h-4" />
                                <span>{property.bedrooms} Bed</span>
                              </div>
                              <div className="flex items-center gap-1">
                                <Bath className="w-4 h-4" />
                                <span>{property.bathrooms} Bath</span>
                              </div>
                            </div>
                            <div className="text-xl font-bold text-primary">
                              ${property.price_per_month}<span className="text-sm text-muted-foreground font-normal">/night</span>
                            </div>
                          </div>
                        </div>
                      </Card>
                    </Link>
                  )
                })}
              </div>

              <div className="mt-12 text-center">
                <Link href="/properties">
                  <Button variant="outline" size="lg">
                    View All Properties
                    <ArrowRight className="w-4 h-4 ml-2" />
                  </Button>
                </Link>
              </div>
            </>
          ) : (
            <div className="text-center py-12">
              <p className="text-muted-foreground text-lg">No featured properties available at the moment.</p>
              <Link href="/properties" className="mt-4 inline-block">
                <Button variant="outline">View All Properties</Button>
              </Link>
            </div>
          )}
        </div>
      </section>

      {/* Benefits Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="mb-12 text-center">
            <h2 className="text-4xl sm:text-5xl font-bold text-foreground mb-4">Why Choose Circle Point Homes?</h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              We're committed to making your rental experience smooth, secure, and satisfying.
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-8">
            {benefits.map((benefit, index) => {
              const Icon = benefit.icon
              return (
                <Card key={index} className="p-8 text-center bg-card border-border hover:border-primary/50 transition-colors">
                  <div className="flex justify-center mb-6">
                    <div className="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
                      <Icon className="w-8 h-8 text-primary" />
                    </div>
                  </div>
                  <h3 className="text-xl font-semibold text-foreground mb-3">{benefit.title}</h3>
                  <p className="text-muted-foreground leading-relaxed">{benefit.description}</p>
                </Card>
              )
            })}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8 bg-primary/5 border-y border-border">
        <div className="max-w-4xl mx-auto text-center space-y-8">
          <div>
            <h2 className="text-4xl sm:text-5xl font-bold text-foreground mb-4">Have a Property to List?</h2>
            <p className="text-lg text-muted-foreground">
              Join our network of property managers and start earning. Simple application, fast approval, start listing in 24 hours.
            </p>
          </div>
          <Link href="/list-property">
            <Button size="lg" className="bg-accent hover:bg-accent/90 text-accent-foreground">
              Apply to List Your Property
              <ArrowRight className="w-4 h-4 ml-2" />
            </Button>
          </Link>
        </div>
      </section>

      <Footer />
    </div>
  )
}
