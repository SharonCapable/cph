'use client'

import { Button } from '@/components/ui/button'
import Link from 'next/link'
import { useState } from 'react'
import { Menu, X, User, LogOut } from 'lucide-react'
import Image from 'next/image'
import { useAuth } from '@/lib/auth-context'

export default function Navigation() {
  const [isOpen, setIsOpen] = useState(false)
  const { user, logout } = useAuth()

  const handleLogout = async () => {
    await logout()
    window.location.href = '/'
  }

  return (
    <nav className="sticky top-0 z-50 w-full bg-background/95 backdrop-blur-sm border-b border-border">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo */}
          <Link href="/" className="flex items-center gap-2">
            <Image
              src="/logo.png.png"
              alt="Circle Point Homes"
              width={40}
              height={40}
              className="h-10 w-10 object-contain"
            />
            <span className="font-bold text-xl hidden sm:inline">Circle Point</span>
          </Link>

          {/* Desktop Navigation */}
          <div className="hidden md:flex items-center gap-6">
            <Link href="/properties" className="text-sm font-medium text-foreground hover:text-primary transition-colors">
              Browse
            </Link>
            <Link href="/contact" className="text-sm font-medium text-foreground hover:text-primary transition-colors">
              Contact
            </Link>
            <Link href="/list-property">
              <Button size="sm" variant="outline">List Property</Button>
            </Link>

            {user ? (
              <>
                {user.role === 'super_admin' && (
                  <a href="https://circlepointhomes.apartments/admin" target="_blank" rel="noopener noreferrer">
                    <Button size="sm" variant="outline">Admin Dashboard</Button>
                  </a>
                )}
                {user.role === 'property_manager' && (
                  <Link href="/manager/properties">
                    <Button size="sm" variant="outline">My Properties</Button>
                  </Link>
                )}
                <div className="flex items-center gap-3">
                  <span className="text-sm text-muted-foreground">{user.full_name}</span>
                  <Button size="sm" variant="ghost" onClick={handleLogout}>
                    <LogOut className="w-4 h-4 mr-2" />
                    Logout
                  </Button>
                </div>
              </>
            ) : (
              <Link href="/login">
                <Button size="sm">
                  <User className="w-4 h-4 mr-2" />
                  Sign In
                </Button>
              </Link>
            )}
          </div>

          {/* Mobile Menu Button */}
          <button
            className="md:hidden"
            onClick={() => setIsOpen(!isOpen)}
            aria-label="Toggle menu"
          >
            {isOpen ? (
              <X className="w-6 h-6 text-foreground" />
            ) : (
              <Menu className="w-6 h-6 text-foreground" />
            )}
          </button>
        </div>

        {/* Mobile Navigation */}
        {isOpen && (
          <div className="md:hidden pb-4 space-y-3">
            <Link
              href="/properties"
              className="block px-4 py-2 text-sm font-medium text-foreground hover:text-primary transition-colors"
              onClick={() => setIsOpen(false)}
            >
              Browse
            </Link>
            <Link
              href="/contact"
              className="block px-4 py-2 text-sm font-medium text-foreground hover:text-primary transition-colors"
              onClick={() => setIsOpen(false)}
            >
              Contact
            </Link>
            <Link href="/list-property" onClick={() => setIsOpen(false)}>
              <Button className="w-full mb-2" variant="outline">List Property</Button>
            </Link>

            {user ? (
              <>
                {user.role === 'super_admin' && (
                  <a href="https://circlepointhomes.apartments/admin" target="_blank" rel="noopener noreferrer" onClick={() => setIsOpen(false)}>
                    <Button className="w-full mb-2" variant="outline">Admin Dashboard</Button>
                  </a>
                )}
                {user.role === 'property_manager' && (
                  <Link href="/manager/properties" onClick={() => setIsOpen(false)}>
                    <Button className="w-full mb-2" variant="outline">My Properties</Button>
                  </Link>
                )}
                <div className="px-4 py-2 text-sm text-muted-foreground">
                  {user.full_name}
                </div>
                <Button className="w-full" variant="ghost" onClick={handleLogout}>
                  <LogOut className="w-4 h-4 mr-2" />
                  Logout
                </Button>
              </>
            ) : (
              <Link href="/login" onClick={() => setIsOpen(false)}>
                <Button className="w-full">
                  <User className="w-4 h-4 mr-2" />
                  Sign In
                </Button>
              </Link>
            )}
          </div>
        )}
      </div>
    </nav>
  )
}
