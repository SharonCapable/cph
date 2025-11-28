'use client'

import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { X } from 'lucide-react'
import { bookingsService } from '@/lib/services'
import { useAuth } from '@/lib/auth-context'

interface BookingDialogProps {
  propertyId: string
  propertyTitle: string
  pricePerNight: number
  onClose: () => void
}

export default function BookingDialog({ propertyId, propertyTitle, pricePerNight, onClose }: BookingDialogProps) {
  const { user } = useAuth()
  const [step, setStep] = useState(1)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const [formData, setFormData] = useState({
    check_in: '',
    check_out: '',
    guests: '1',
    phone: user?.phone || '',
    message: '',
    guest_full_name: user?.full_name || '',
    guest_email: user?.email || '',
    guest_nationality: '',
    guest_date_of_birth: '',
    guest_gender: '',
    guest_passport_number: '',
    guest_address: '',
    purpose_of_visit: '',
    arrival_date: '',
    arrival_flight: '',
    departure_date: '',
    departure_flight: '',
    emergency_contact_name: '',
    emergency_contact_relationship: '',
    emergency_contact_phone: '',
    emergency_contact_email: '',
    is_foreigner: false,
    requires_visa_letter: false,
    terms_accepted: false,
  })

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target
    const checked = (e.target as HTMLInputElement).checked

    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }))
  }

  const calculateNights = () => {
    if (formData.check_in && formData.check_out) {
      const checkIn = new Date(formData.check_in)
      const checkOut = new Date(formData.check_out)
      const nights = Math.ceil((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24))
      return nights > 0 ? nights : 0
    }
    return 0
  }

  const totalPrice = calculateNights() * pricePerNight

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (step === 1) {
      setStep(2)
      return
    }

    if (step === 2) {
      setStep(3)
      return
    }

    if (!formData.terms_accepted) {
      setError('You must accept the terms and conditions')
      return
    }

    setLoading(true)
    setError('')

    try {
      const result = await bookingsService.create({
        property_id: propertyId,
        ...formData,
        signature_data: formData.guest_full_name,
      })

      if (result.success) {
        alert('Booking request submitted successfully! You will receive a confirmation email shortly.')
        onClose()
      } else {
        setError(result.message || 'Failed to submit booking')
      }
    } catch (err: any) {
      setError(err.message || 'Failed to submit booking')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 overflow-y-auto">
      <Card className="w-full max-w-3xl bg-card border-border my-8">
        <div className="flex items-center justify-between p-6 border-b border-border">
          <div>
            <h2 className="text-2xl font-bold text-foreground">Book Property</h2>
            <p className="text-sm text-muted-foreground mt-1">Step {step} of 3</p>
          </div>
          <button onClick={onClose} className="text-muted-foreground hover:text-foreground">
            <X className="w-6 h-6" />
          </button>
        </div>

        <div className="p-6 max-h-[70vh] overflow-y-auto">
          {error && (
            <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
              <p className="text-red-600 text-sm">{error}</p>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-6">
            {step === 1 && (
              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label>Check-in *</Label>
                    <Input name="check_in" type="date" required value={formData.check_in} onChange={handleChange} className="mt-2" />
                  </div>
                  <div>
                    <Label>Check-out *</Label>
                    <Input name="check_out" type="date" required value={formData.check_out} onChange={handleChange} className="mt-2" />
                  </div>
                </div>

                {calculateNights() > 0 && (
                  <div className="p-4 bg-primary/5 rounded-lg">
                    <p><strong>{calculateNights()} nights</strong> × ${pricePerNight} = <strong className="text-primary">${totalPrice}</strong></p>
                  </div>
                )}

                <div><Label>Full Name *</Label><Input name="guest_full_name" required value={formData.guest_full_name} onChange={handleChange} className="mt-2" /></div>
                <div><Label>Email *</Label><Input name="guest_email" type="email" required value={formData.guest_email} onChange={handleChange} className="mt-2" /></div>
                <div><Label>Phone *</Label><Input name="phone" required value={formData.phone} onChange={handleChange} className="mt-2" /></div>
                <div><Label>Nationality *</Label><Input name="guest_nationality" required value={formData.guest_nationality} onChange={handleChange} className="mt-2" /></div>
                <div><Label>Date of Birth *</Label><Input name="guest_date_of_birth" type="date" required value={formData.guest_date_of_birth} onChange={handleChange} className="mt-2" /></div>
                <div><Label>Passport Number *</Label><Input name="guest_passport_number" required value={formData.guest_passport_number} onChange={handleChange} className="mt-2" /></div>

                <div className="flex gap-2"><input type="checkbox" name="is_foreigner" checked={formData.is_foreigner} onChange={handleChange} /><Label>I am a foreigner (non-Ghanaian)</Label></div>
                {formData.is_foreigner && <div className="flex gap-2 pl-6"><input type="checkbox" name="requires_visa_letter" checked={formData.requires_visa_letter} onChange={handleChange} /><Label>I need a visa invitation letter</Label></div>}
              </div>
            )}

            {step === 2 && (
              <div className="space-y-4">
                <div><Label>Purpose of Visit *</Label><Input name="purpose_of_visit" required value={formData.purpose_of_visit} onChange={handleChange} className="mt-2" /></div>
                <div><Label>Emergency Contact Name *</Label><Input name="emergency_contact_name" required value={formData.emergency_contact_name} onChange={handleChange} className="mt-2" /></div>
                <div><Label>Emergency Contact Phone *</Label><Input name="emergency_contact_phone" required value={formData.emergency_contact_phone} onChange={handleChange} className="mt-2" /></div>
                <div><Label>Additional Comments</Label><Textarea name="message" value={formData.message} onChange={handleChange} className="mt-2" /></div>
              </div>
            )}

            {step === 3 && (
              <div className="space-y-4">
                <div className="bg-muted/50 p-4 rounded-lg">
                  <h3 className="font-semibold mb-2">Booking Summary</h3>
                  <p>Guest: {formData.guest_full_name}</p>
                  <p>Dates: {formData.check_in} to {formData.check_out}</p>
                  <p>Total: ${totalPrice}</p>
                </div>

                <div className="flex gap-2">
                  <input type="checkbox" name="terms_accepted" checked={formData.terms_accepted} onChange={handleChange} required />
                  <Label>I agree to terms and conditions *</Label>
                </div>
              </div>
            )}

            <div className="flex gap-4 pt-6 border-t">
              {step > 1 && <Button type="button" variant="outline" onClick={() => setStep(step - 1)} className="flex-1">Back</Button>}
              <Button type="submit" disabled={loading} className="flex-1">{loading ? 'Submitting...' : step === 3 ? 'Submit Booking' : 'Continue'}</Button>
            </div>
          </form>
        </div>
      </Card>
    </div>
  )
}
