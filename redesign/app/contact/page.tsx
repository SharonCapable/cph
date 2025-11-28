'use client'

import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Mail, Phone, MapPin, Clock, Send, MessageCircle, Linkedin, Instagram } from 'lucide-react'
import Navigation from '@/components/navigation'
import Footer from '@/components/footer'

export default function ContactPage() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    subject: '',
    message: '',
  })
  const [submitted, setSubmitted] = useState(false)

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target
    setFormData((prev) => ({ ...prev, [name]: value }))
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    setSubmitted(true)
    setTimeout(() => {
      setFormData({ name: '', email: '', subject: '', message: '' })
      setSubmitted(false)
    }, 3000)
  }

  const contactMethods = [
    {
      icon: MessageCircle,
      label: 'WhatsApp',
      description: 'Quick response',
      link: 'https://wa.me/233207119731',
      color: 'bg-green-100 text-green-700',
    },
    {
      icon: Linkedin,
      label: 'LinkedIn',
      description: 'Professional inquiries',
      link: 'https://linkedin.com/in/francis-curtis-stevens-33839a311',
      color: 'bg-blue-100 text-blue-700',
    },
    {
      icon: Instagram,
      label: 'Instagram',
      description: 'Updates and gallery',
      link: 'https://instagram.com/curtis_onecirclepointhomes1',
      color: 'bg-pink-100 text-pink-700',
    },
  ]

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Navigation />

      <main className="flex-1">
        {/* Hero Section */}
        <section className="bg-primary/5 border-b border-border py-16 px-4 sm:px-6 lg:px-8">
          <div className="max-w-4xl mx-auto text-center">
            <h1 className="text-4xl sm:text-5xl font-bold text-foreground mb-4">Get in Touch</h1>
            <p className="text-lg text-muted-foreground">
              Have questions? Reach out through your preferred channel and we'll get back to you promptly.
            </p>
          </div>
        </section>

        {/* Content */}
        <section className="py-12 px-4 sm:px-6 lg:px-8">
          <div className="max-w-6xl mx-auto">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              {/* Contact Information */}
              <div className="lg:col-span-1 space-y-6">
                <Card className="p-6 bg-card border-border">
                  <div className="flex gap-4">
                    <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                      <Mail className="w-6 h-6 text-primary" />
                    </div>
                    <div>
                      <h4 className="font-semibold text-foreground mb-1">Email</h4>
                      <a href="mailto:info@circlepointhomes.apartments" className="text-muted-foreground hover:text-primary transition">
                        info@circlepointhomes.apartments
                      </a>
                    </div>
                  </div>
                </Card>

                <Card className="p-6 bg-card border-border">
                  <div className="flex gap-4">
                    <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                      <Phone className="w-6 h-6 text-primary" />
                    </div>
                    <div>
                      <h4 className="font-semibold text-foreground mb-1">Phone</h4>
                      <a href="tel:+233207119731" className="text-muted-foreground hover:text-primary transition">
                        +233 (0) 20 711 9731
                      </a>
                    </div>
                  </div>
                </Card>

                <Card className="p-6 bg-card border-border">
                  <div className="flex gap-4">
                    <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                      <MapPin className="w-6 h-6 text-primary" />
                    </div>
                    <div>
                      <h4 className="font-semibold text-foreground mb-1">Address</h4>
                      <p className="text-muted-foreground">Accra, Ghana</p>
                    </div>
                  </div>
                </Card>

                <Card className="p-6 bg-card border-border">
                  <div className="flex gap-4">
                    <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                      <Clock className="w-6 h-6 text-primary" />
                    </div>
                    <div>
                      <h4 className="font-semibold text-foreground mb-1">Hours</h4>
                      <p className="text-muted-foreground text-sm">Mon - Fri: 9AM - 6PM<br />Sat - Sun: 10AM - 4PM</p>
                    </div>
                  </div>
                </Card>

                <div>
                  <h4 className="font-semibold text-foreground mb-3">Connect With Us</h4>
                  <div className="grid grid-cols-1 gap-3">
                    {contactMethods.map((method) => {
                      const Icon = method.icon
                      return (
                        <a
                          key={method.label}
                          href={method.link}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center gap-3 p-4 border border-border rounded-lg hover:border-primary/50 hover:bg-primary/5 transition"
                        >
                          <div className={`w-10 h-10 rounded-lg ${method.color} flex items-center justify-center flex-shrink-0`}>
                            <Icon className="w-5 h-5" />
                          </div>
                          <div className="min-w-0">
                            <p className="font-semibold text-foreground text-sm">{method.label}</p>
                            <p className="text-xs text-muted-foreground">{method.description}</p>
                          </div>
                        </a>
                      )
                    })}
                  </div>
                </div>
              </div>

              {/* Contact Form */}
              <div className="lg:col-span-2">
                <Card className="p-8 bg-card border-border">
                  <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-semibold text-foreground mb-2">Name *</label>
                        <input
                          type="text"
                          name="name"
                          value={formData.name}
                          onChange={handleInputChange}
                          placeholder="Your name"
                          className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                          required
                        />
                      </div>

                      <div>
                        <label className="block text-sm font-semibold text-foreground mb-2">Email *</label>
                        <input
                          type="email"
                          name="email"
                          value={formData.email}
                          onChange={handleInputChange}
                          placeholder="your@email.com"
                          className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                          required
                        />
                      </div>
                    </div>

                    <div>
                      <label className="block text-sm font-semibold text-foreground mb-2">Subject *</label>
                      <input
                        type="text"
                        name="subject"
                        value={formData.subject}
                        onChange={handleInputChange}
                        placeholder="How can we help?"
                        className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary"
                        required
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-semibold text-foreground mb-2">Message *</label>
                      <textarea
                        name="message"
                        value={formData.message}
                        onChange={handleInputChange}
                        placeholder="Tell us more about your inquiry..."
                        rows={6}
                        className="w-full px-4 py-3 border border-border rounded-lg bg-input text-foreground focus:outline-none focus:border-primary resize-none"
                        required
                      />
                    </div>

                    <Button
                      type="submit"
                      className="w-full bg-accent hover:bg-accent/90 text-accent-foreground font-semibold h-12 flex items-center justify-center gap-2"
                    >
                      {submitted ? 'Message Sent!' : 'Send Message'}
                      {!submitted && <Send className="w-4 h-4" />}
                    </Button>
                  </form>
                </Card>
              </div>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  )
}
