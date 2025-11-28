import Link from 'next/link'
import { Mail, MessageCircle, Linkedin, Instagram } from 'lucide-react'

export default function Footer() {
  return (
    <footer className="bg-foreground text-background border-t border-border">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
          {/* Brand */}
          <div>
            <div className="flex items-center gap-2 mb-4">
              <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-accent to-accent flex items-center justify-center">
                <span className="text-foreground font-bold text-lg">CP</span>
              </div>
              <span className="font-bold text-lg">Circle Point</span>
            </div>
            <p className="text-sm opacity-80">
              Connecting travelers with quality homes around the world.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="font-semibold mb-4">Quick Links</h4>
            <ul className="space-y-2 text-sm">
              <li><Link href="/properties" className="opacity-80 hover:opacity-100 transition">Browse Properties</Link></li>
              <li><Link href="/list-property" className="opacity-80 hover:opacity-100 transition">List Property</Link></li>
              <li><Link href="/contact" className="opacity-80 hover:opacity-100 transition">Contact</Link></li>
            </ul>
          </div>

          {/* Get in Touch */}
          <div>
            <h4 className="font-semibold mb-4">Get in Touch</h4>
            <div className="space-y-3">
              <div className="flex items-center gap-3">
                <a href="https://wa.me/233207119731" target="_blank" rel="noopener noreferrer" className="opacity-80 hover:opacity-100 transition flex items-center gap-2">
                  <MessageCircle className="w-4 h-4" />
                  <span className="text-sm">WhatsApp</span>
                </a>
              </div>
              <div className="flex items-center gap-3">
                <a href="https://linkedin.com/in/francis-curtis-stevens-33839a311" target="_blank" rel="noopener noreferrer" className="opacity-80 hover:opacity-100 transition flex items-center gap-2">
                  <Linkedin className="w-4 h-4" />
                  <span className="text-sm">LinkedIn</span>
                </a>
              </div>
              <div className="flex items-center gap-3">
                <a href="https://instagram.com/curtis_onecirclepointhomes1" target="_blank" rel="noopener noreferrer" className="opacity-80 hover:opacity-100 transition flex items-center gap-2">
                  <Instagram className="w-4 h-4" />
                  <span className="text-sm">Instagram</span>
                </a>
              </div>
              <div className="flex items-start gap-3 pt-2 border-t border-background/20">
                <Mail className="w-4 h-4 mt-0.5 flex-shrink-0" />
                <a href="mailto:info@circlepointhomes.apartments" className="opacity-80 hover:opacity-100 transition text-sm">
                  info@circlepointhomes.apartments
                </a>
              </div>
            </div>
          </div>
        </div>

        <div className="border-t border-background/20 pt-8 text-center text-sm opacity-80">
          <p>© 2025 Circle Point Homes. All rights reserved.</p>
        </div>
      </div>
    </footer>
  )
}
