import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Mail, Phone, MapPin, MessageCircle } from 'lucide-react'
import Navigation from '@/components/navigation'
import Footer from '@/components/footer'

export default function ContactPage() {
  const contactInfo = [
    {
      icon: Phone,
      title: 'Phone',
      value: '+233 20 711 9731',
      href: 'tel:+233207119731',
    },
    {
      icon: MessageCircle,
      title: 'WhatsApp',
      value: '+233 20 711 9731',
      href: 'https://wa.me/233207119731',
    },
    {
      icon: Mail,
      title: 'Email',
      value: 'admin@circlepointhomes.apartments',
      href: 'mailto:admin@circlepointhomes.apartments',
    },
    {
      icon: MapPin,
      title: 'Location',
      value: 'Accra, Ghana',
      href: '#',
    },
  ]

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Navigation />

      <section className="bg-primary/5 border-b border-border py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto text-center">
          <h1 className="text-4xl sm:text-5xl font-bold text-foreground mb-4">Get in Touch</h1>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Have questions? We would love to hear from you.
          </p>
        </div>
      </section>

      <div className="flex-1 py-16 px-4 sm:px-6 lg:px-8">
        <div className="max-w-4xl mx-auto">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {contactInfo.map((item, index) => {
              const Icon = item.icon
              return (
                <Card key={index} className="p-6 bg-card border-border hover:border-primary/50 transition-colors text-center">
                  <a
                    href={item.href}
                    className="flex flex-col items-center gap-3"
                    target={item.href.startsWith('http') ? '_blank' : undefined}
                    rel={item.href.startsWith('http') ? 'noopener noreferrer' : undefined}
                  >
                    <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                      <Icon className="w-6 h-6 text-primary" />
                    </div>
                    <div>
                      <h3 className="font-semibold text-foreground mb-1">{item.title}</h3>
                      <p className="text-sm text-muted-foreground">{item.value}</p>
                    </div>
                  </a>
                </Card>
              )
            })}
          </div>
        </div>
      </div>

      <Footer />
    </div>
  )
}
