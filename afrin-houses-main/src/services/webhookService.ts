import { PropertyFilters } from './propertyService';

export interface WebhookPayload {
  searchQuery: string;
  filters: PropertyFilters;
  results: any[];
  timestamp: string;
  source: 'home_page' | 'search_page';
}

class WebhookService {
  public webhookUrl = 'https://n8n.besttrend-sy.com/webhook-test/e9393399-09d1-4e24-b54b-6c30ebc01d0d';

  // Test webhook availability
  async testWebhookConnection(): Promise<boolean> {
    try {
      console.log('🔍 Testing webhook connection...');
      const response = await fetch(this.webhookUrl, {
        method: 'GET',
        mode: 'no-cors'
      });
      
      console.log('✅ Webhook connection test completed (no-cors mode)');
      return true; // في وضع no-cors، نعتبر الطلب ناجح إذا لم يحدث خطأ
    } catch (error) {
      console.error('❌ Webhook connection test failed:', error);
      return false;
    }
  }

  /**
   * Send search results to webhook
   */
  async sendSearchResults(payload: WebhookPayload): Promise<boolean> {
    try {
      console.log('📤 Sending search results to webhook:', payload);
      
      // إنشاء URL مع المعاملات للـ GET request
      const urlParams = new URLSearchParams({
        searchQuery: payload.searchQuery,
        listingType: payload.filters.listingType || 'all',
        propertyType: payload.filters.propertyType || 'all',
        timestamp: payload.timestamp,
        source: payload.source,
        results: JSON.stringify(payload.results)
      });
      
      const fullUrl = `${this.webhookUrl}?${urlParams.toString()}`;
      
      // استخدام GET method بدلاً من POST
      const response = await fetch(fullUrl, {
        method: 'GET',
        mode: 'no-cors'
      });

      console.log('✅ Search results sent (GET method) - request completed');
      
      // في وضع no-cors، لا يمكننا قراءة الاستجابة، لكن الطلب تم إرساله
      return true;
      
    } catch (error) {
      console.error('❌ Failed to send search results (GET method):', error);
      return false;
    }
  }

  /**
   * Send search query and filters to webhook (before getting results)
   */
  async sendSearchQuery(searchQuery: string, filters: PropertyFilters, source: 'home_page' | 'search_page' = 'home_page'): Promise<boolean> {
    try {
      const payload: Omit<WebhookPayload, 'results'> = {
        searchQuery,
        filters,
        timestamp: new Date().toISOString(),
        source,
      };

      console.log('📤 Sending search query to webhook:', payload);
      
      // إنشاء URL مع المعاملات للـ GET request
      const urlParams = new URLSearchParams({
        searchQuery: payload.searchQuery,
        listingType: payload.filters.listingType || 'all',
        propertyType: payload.filters.propertyType || 'all',
        timestamp: payload.timestamp,
        source: payload.source
      });
      
      const fullUrl = `${this.webhookUrl}?${urlParams.toString()}`;
      
      // استخدام GET method بدلاً من POST
      const response = await fetch(fullUrl, {
        method: 'GET',
        mode: 'no-cors'
      });

      console.log('✅ Search query sent (GET method) - request completed');
      
      // في وضع no-cors، لا يمكننا قراءة الاستجابة، لكن الطلب تم إرساله
      return true;
      
    } catch (error) {
      console.error('❌ Failed to send search query (GET method):', error);
      return false;
    }
  }

  // طريقة بديلة لإرسال البيانات عبر GET مع معاملات مبسطة
  private async sendViaSimpleGet(data: any): Promise<void> {
    const simpleParams = new URLSearchParams({
      query: data.searchQuery || '',
      type: data.filters?.listingType || 'all',
      source: data.source || 'home_page'
    });
    
    const simpleUrl = `${this.webhookUrl}?${simpleParams.toString()}`;
    
    await fetch(simpleUrl, {
      method: 'GET',
      mode: 'no-cors'
    });
  }
}

export const webhookService = new WebhookService();
export default webhookService;