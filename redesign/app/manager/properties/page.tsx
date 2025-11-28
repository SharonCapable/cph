'use client'

import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Plus, Trash2, Edit2, MapPin, Bed, Bath } from 'lucide-react'
import Navigation from '@/components/navigation'
import Footer from '@/components/footer'
import Link from 'next/link'

interface Property {
  id: string
  title: string
  location: string
  beds: number
  baths: number
  price: number
  image: string
  status: 'active' | 'inactive'
  bookings: number
}

export default function ManagerPropertiesPage() {
  const [properties, setProperties] = useState<Property[]>([
    {
      id: '1',
      title: 'Luxury 2-Bedroom Apartment',
      location: 'Accra, Ghana',
      beds: 2,
      baths: 2,
      price: 85,
      image: '/luxury-apartment-living-room.png',
      status: 'active',
      bookings: 3,
    },
  ])

  const [showDeleteConfirm, setShowDeleteConfirm] = useState<string | null>(null)

  const handleDelete = (id: string) => {
    setProperties(props => props.filter(p => p.id !== id))
    setShowDeleteConfirm(null)
  }

  const toggleStatus = (id: string) => {
    setProperties(props =>
      props.map(p =>
        p.id === id ? { ...p, status: p.status === 'active' ? 'inactive' : 'active' } : p
      )
    )
  }

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Navigation />

      <main className="flex-1 py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-6xl mx-auto">
          {/* Header */}
          <div className="flex justify-between items-start mb-12">
            <div>
              <h1 className="text-4xl sm:text-5xl font-bold text-foreground mb-2">My Properties</h1>
              <p className="text-lg text-muted-foreground">Manage and monitor your listed properties</p>
            </div>
            <Link href="/manager/properties/new">
              <Button className="gap-2">
                <Plus className="w-5 h-5" />
                Add Property
              </Button>
            </Link>
          </div>

          {/* Properties Grid */}
          {properties.length === 0 ? (
            <Card className="p-12 text-center bg-card border-border">
              <p className="text-muted-foreground mb-6">You haven't listed any properties yet</p>
              <Link href="/manager/properties/new">
                <Button>Add Your First Property</Button>
              </Link>
            </Card>
          ) : (
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {properties.map((property) => (
                <Card key={property.id} className="overflow-hidden bg-card border-border hover:border-primary/50 transition">
                  <div className="relative h-48 bg-muted overflow-hidden">
                    <img
                      src={property.image || "/placeholder.svg"}
                      alt={property.title}
                      className="w-full h-full object-cover"
                    />
                    <div className="absolute top-4 right-4">
                      <span
                        className={`px-3 py-1 rounded-full text-xs font-semibold ${
                          property.status === 'active'
                            ? 'bg-green-100 text-green-800'
                            : 'bg-gray-100 text-gray-800'
                        }`}
                      >
                        {property.status === 'active' ? 'Active' : 'Inactive'}
                      </span>
                    </div>
                  </div>

                  <div className="p-6 space-y-4">
                    <div>
                      <h3 className="font-semibold text-foreground text-lg mb-2">{property.title}</h3>
                      <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <MapPin className="w-4 h-4" />
                        {property.location}
                      </div>
                    </div>

                    <div className="flex gap-6 text-sm text-muted-foreground">
                      <div className="flex items-center gap-2">
                        <Bed className="w-4 h-4" />
                        {property.beds} Bed
                      </div>
                      <div className="flex items-center gap-2">
                        <Bath className="w-4 h-4" />
                        {property.baths} Bath
                      </div>
                      <div className="font-semibold text-foreground">
                        ${property.price}/night
                      </div>
                    </div>

                    <div className="pt-4 border-t border-border">
                      <p className="text-sm text-muted-foreground mb-4">
                        <span className="font-semibold text-foreground">{property.bookings}</span> active bookings
                      </p>

                      <div className="flex gap-3">
                        <Button
                          variant="outline"
                          size="sm"
                          className="flex-1 gap-2"
                          onClick={() => toggleStatus(property.id)}
                        >
                          <Edit2 className="w-4 h-4" />
                          {property.status === 'active' ? 'Deactivate' : 'Activate'}
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          className="flex-1 gap-2 text-red-600 border-red-200 hover:bg-red-50"
                          onClick={() => setShowDeleteConfirm(property.id)}
                        >
                          <Trash2 className="w-4 h-4" />
                          Delete
                        </Button>
                      </div>
                    </div>
                  </div>

                  {showDeleteConfirm === property.id && (
                    <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
                      <Card className="max-w-sm bg-card border-border p-6 space-y-4">
                        <h3 className="font-bold text-foreground text-lg">Delete Property?</h3>
                        <p className="text-muted-foreground">This action cannot be undone.</p>
                        <div className="flex gap-3">
                          <Button
                            variant="outline"
                            className="flex-1"
                            onClick={() => setShowDeleteConfirm(null)}
                          >
                            Cancel
                          </Button>
                          <Button
                            className="flex-1 bg-red-600 hover:bg-red-700"
                            onClick={() => handleDelete(property.id)}
                          >
                            Delete
                          </Button>
                        </div>
                      </Card>
                    </div>
                  )}
                </Card>
              ))}
            </div>
          )}
        </div>
      </main>

      <Footer />
    </div>
  )
}
