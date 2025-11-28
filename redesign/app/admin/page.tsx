'use client'

import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { CheckCircle2, XCircle, Clock, Users, Home, FileText } from 'lucide-react'
import Navigation from '@/components/navigation'
import Footer from '@/components/footer'

interface Application {
  id: string
  name: string
  email: string
  phone: string
  propertiesCount: string
  experience: string
  description: string
  status: 'pending' | 'approved' | 'rejected'
  submittedAt: string
}

export default function AdminDashboard() {
  const [applications, setApplications] = useState<Application[]>([
    {
      id: '1',
      name: 'John Kwame',
      email: 'john@example.com',
      phone: '+233 123 456 789',
      propertiesCount: '2-5 properties',
      experience: '3-5 years',
      description: 'I manage luxury apartments in Accra...',
      status: 'pending',
      submittedAt: '2025-01-15',
    },
  ])

  const [selectedTab, setSelectedTab] = useState<'pending' | 'approved' | 'rejected'>('pending')

  const handleApprove = (id: string) => {
    setApplications(apps =>
      apps.map(app =>
        app.id === id ? { ...app, status: 'approved' } : app
      )
    )
  }

  const handleReject = (id: string) => {
    setApplications(apps =>
      apps.map(app =>
        app.id === id ? { ...app, status: 'rejected' } : app
      )
    )
  }

  const pendingApps = applications.filter(app => app.status === 'pending')
  const approvedApps = applications.filter(app => app.status === 'approved')
  const rejectedApps = applications.filter(app => app.status === 'rejected')

  const stats = [
    { icon: Clock, label: 'Pending', value: pendingApps.length, color: 'text-yellow-600' },
    { icon: CheckCircle2, label: 'Approved', value: approvedApps.length, color: 'text-green-600' },
    { icon: XCircle, label: 'Rejected', value: rejectedApps.length, color: 'text-red-600' },
    { icon: Users, label: 'Total Applications', value: applications.length, color: 'text-blue-600' },
  ]

  const tabs = [
    { id: 'pending', label: 'Pending Review', count: pendingApps.length },
    { id: 'approved', label: 'Approved', count: approvedApps.length },
    { id: 'rejected', label: 'Rejected', count: rejectedApps.length },
  ]

  const currentApps = selectedTab === 'pending' ? pendingApps : selectedTab === 'approved' ? approvedApps : rejectedApps

  return (
    <div className="min-h-screen flex flex-col bg-background">
      <Navigation />

      <main className="flex-1 py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          {/* Header */}
          <div className="mb-12">
            <h1 className="text-4xl sm:text-5xl font-bold text-foreground mb-2">Super Admin Dashboard</h1>
            <p className="text-lg text-muted-foreground">Review and manage property manager applications</p>
          </div>

          {/* Stats */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            {stats.map((stat, index) => {
              const Icon = stat.icon
              return (
                <Card key={index} className="p-6 bg-card border-border">
                  <div className="flex items-center justify-between mb-4">
                    <Icon className={`w-8 h-8 ${stat.color}`} />
                  </div>
                  <div className="text-3xl font-bold text-foreground mb-1">{stat.value}</div>
                  <p className="text-sm text-muted-foreground">{stat.label}</p>
                </Card>
              )
            })}
          </div>

          {/* Tabs */}
          <div className="flex gap-2 mb-6 border-b border-border">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                onClick={() => setSelectedTab(tab.id as any)}
                className={`px-4 py-3 font-semibold text-sm border-b-2 transition ${
                  selectedTab === tab.id
                    ? 'border-primary text-primary'
                    : 'border-transparent text-muted-foreground hover:text-foreground'
                }`}
              >
                {tab.label} {tab.count > 0 && <span className="ml-2 bg-primary/20 text-primary px-2 py-1 rounded-full text-xs">{tab.count}</span>}
              </button>
            ))}
          </div>

          {/* Applications List */}
          <div className="space-y-4">
            {currentApps.length === 0 ? (
              <Card className="p-12 text-center bg-card border-border">
                <FileText className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                <p className="text-muted-foreground">No applications in this category</p>
              </Card>
            ) : (
              currentApps.map((app) => (
                <Card key={app.id} className="p-6 bg-card border-border hover:border-primary/50 transition">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-3">
                      <div>
                        <h3 className="font-semibold text-foreground text-lg">{app.name}</h3>
                        <p className="text-sm text-muted-foreground">{app.email}</p>
                      </div>
                      <div>
                        <p className="text-sm text-muted-foreground">
                          <span className="font-semibold text-foreground">Phone:</span> {app.phone}
                        </p>
                        <p className="text-sm text-muted-foreground">
                          <span className="font-semibold text-foreground">Experience:</span> {app.experience}
                        </p>
                        <p className="text-sm text-muted-foreground">
                          <span className="font-semibold text-foreground">Properties:</span> {app.propertiesCount}
                        </p>
                      </div>
                    </div>

                    <div className="flex flex-col justify-between">
                      <div className="text-sm text-muted-foreground">
                        <p className="mb-2">{app.description}</p>
                        <p className="text-xs">Submitted: {app.submittedAt}</p>
                      </div>

                      {app.status === 'pending' && (
                        <div className="flex gap-3 mt-4">
                          <Button
                            variant="outline"
                            size="sm"
                            className="flex-1 text-red-600 border-red-200 hover:bg-red-50"
                            onClick={() => handleReject(app.id)}
                          >
                            Reject
                          </Button>
                          <Button
                            size="sm"
                            className="flex-1 bg-green-600 hover:bg-green-700 text-white"
                            onClick={() => handleApprove(app.id)}
                          >
                            Approve
                          </Button>
                        </div>
                      )}

                      {app.status === 'approved' && (
                        <div className="flex items-center gap-2 text-green-600">
                          <CheckCircle2 className="w-5 h-5" />
                          <span className="text-sm font-semibold">Approved</span>
                        </div>
                      )}

                      {app.status === 'rejected' && (
                        <div className="flex items-center gap-2 text-red-600">
                          <XCircle className="w-5 h-5" />
                          <span className="text-sm font-semibold">Rejected</span>
                        </div>
                      )}
                    </div>
                  </div>
                </Card>
              ))
            )}
          </div>
        </div>
      </main>

      <Footer />
    </div>
  )
}
