'use client'

import { useState, useEffect, Suspense } from 'react'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { MapPin, Bed, Bath, Search } from 'lucide-react'
import Link from 'next/link'
import Navigation from '@/components/navigation'
import Footer from '@/components/footer'
import { propertiesService } from '@/lib/services'
import { Property } from '@/lib/types'
import { getImageUrl } from '@/lib/utils'
import { useSearchParams } from 'next/navigation'

function PropertiesContent() {
  const searchParams = useSearchParams()
  const [properties, setProperties] = useState<Property[]>([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState(searchParams.get('location') || '')
  const [bedroomFilter, setBedroomFilter] = useState(searchParams.get('bedrooms') || 'all')
  const [priceFilter, setPriceFilter] = useState(searchParams.get('price') || 'all')
  const [amenityFilter, setAmenityFilter] = useState<string[]>([])

  useEffect(() => {
    async function loadProperties() {
      try {
        setLoading(true)
        const data = await propertiesService.getAll()
        setProperties(data)
      } catch (error) {
        console.error('Failed to load properties:', error)
      } finally {
        setLoading(false)
      }
    }

    loadProperties()
  }, [])

  const filteredProperties = properties.filter((property) => {
    const matchesSearch = property.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
      property.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (property.location && property.location.toLowerCase().includes(searchTerm.toLowerCase()))

    const matchesBedrooms = bedroomFilter === 'all' ||
      (bedroomFilter === '0' && property.bedrooms === 0) ||
      (bedroomFilter === '1' && property.bedrooms === 1) ||
      (bedroomFilter === '2' && property.bedrooms >= 2)

    let matchesPrice = true
    const priceNum = typeof property.price_per_month === 'string' ? parseFloat(property.price_per_month) : property.price_per_month
    if (priceFilter === 'under50') {
      matchesPrice = priceNum < 50
    } else if (priceFilter === '50to100') {
      matchesPrice = priceNum >= 50 && priceNum <= 100
    } else if (priceFilter === 'over100') {
      matchesPrice = priceNum > 100
    }

    const matchesAmenities = amenityFilter.length === 0 ||
      amenityFilter.every(amenity =>
        property.amenities && property.amenities.includes(amenity)
      )

    return matchesSearch && matchesBedrooms && matchesPrice && matchesAmenities
  })

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Navigation />

      {/* Page Header */}
      <section className="bg-primary/5 border-b border-border py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <h1 className="text-4xl sm:text-5xl font-bold text-foreground mb-2">Browse Properties</h1>
          <p className="text-lg text-muted-foreground">
            Discover {filteredProperties.length} available properties in our network
          </p>
        </div>
      </section>

      {/* Main Content */}
      <div className="flex-1 py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {/* Sidebar Filters */}
            <div className="lg:col-span-1">
              <Card className="p-6 sticky top-24 bg-card border-border">
                <h3 className="text-lg font-semibold text-foreground mb-6">Filters</h3>

                {/* Search */}
                <div className="mb-8">
                  <label className="block text-sm font-medium text-foreground mb-2">Search</label>
                  <div className="flex items-center gap-2 px-3 py-2 bg-input rounded-lg border border-border">
                    <Search className="w-4 h-4 text-muted-foreground" />
                    <input
                      type="text"
                      placeholder="Location or name"
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="flex-1 bg-transparent outline-none text-sm"
                    />
                  </div>
                </div>

                {/* Bedrooms */}
                <div className="mb-8">
                  <label className="block text-sm font-medium text-foreground mb-2">Bedrooms</label>
                  <select
                    value={bedroomFilter}
                    onChange={(e) => setBedroomFilter(e.target.value)}
                    className="w-full px-3 py-2 bg-input border border-border rounded-lg text-sm appearance-none cursor-pointer"
                  >
                    <option value="all">All bedrooms</option>
                    <option value="1">1 Bedroom</option>
                    <option value="2">2 Bedrooms</option>
                    <option value="3">3+ Bedrooms</option>
                  </select>
                </div>

                {/* Price */}
                <div className="mb-8">
                  <label className="block text-sm font-medium text-foreground mb-2">Price Range</label>
                  <select
                    value={priceFilter}
                    onChange={(e) => setPriceFilter(e.target.value)}
                    className="w-full px-3 py-2 bg-input border border-border rounded-lg text-sm appearance-none cursor-pointer"
                  >
                    <option value="all">Any price</option>
                    <option value="under50">Under $50/night</option>
                    <option value="50to100">$50 - $100/night</option>
                    <option value="over100">Over $100/night</option>
                  </select>
                </div>

                {/* Amenities */}
                <div>
                  <label className="block text-sm font-medium text-foreground mb-3">Amenities</label>
                  <div className="space-y-2">
                    {['WiFi', 'Air Conditioning', 'Kitchen', 'Pool', 'Gym', 'Parking'].map((amenity) => (
                      <label key={amenity} className="flex items-center gap-2 cursor-pointer group">
                        <input
                          type="checkbox"
                          checked={amenityFilter.includes(amenity)}
                          onChange={(e) => {
                            if (e.target.checked) {
                              setAmenityFilter([...amenityFilter, amenity])
                            } else {
                              setAmenityFilter(amenityFilter.filter(a => a !== amenity))
                            }
                          }}
                          className="w-4 h-4 rounded border-border text-primary focus:ring-2 focus:ring-primary"
                        />
                        <span className="text-sm text-foreground group-hover:text-primary">{amenity}</span>
                      </label>
                    ))}
                  </div>
                </div>
              </Card>
            </div>

            {/* Property Grid */}
            <div className="lg:col-span-3">
              {loading ? (
                <div className="text-center py-12">
                  <p className="text-muted-foreground">Loading properties...</p>
                </div>
              ) : filteredProperties.length > 0 ? (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                  {filteredProperties.map((property) => {
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
              ) : (
                <Card className="p-12 text-center bg-card border-border">
                  <p className="text-muted-foreground mb-4">No properties found matching your criteria.</p>
                  <Button variant="outline" onClick={() => {
                    setSearchTerm('')
                    setBedroomFilter('all')
                    setPriceFilter('all')
                  }}>
                    Clear Filters
                  </Button>
                </Card>
              )}
            </div>
          </div>
        </div>
      </div>

      <Footer />
    </div>
  )
}

export default function PropertiesPage() {
  return (
    <Suspense fallback={<div>Loading...</div>}>
      <PropertiesContent />
    </Suspense>
  )
}
