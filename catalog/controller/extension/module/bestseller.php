<?php
class ControllerExtensionModuleBestSeller extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/bestseller');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
        
        $this->load->model('account/customerpartner');
		$this->load->model('customerpartner/master');                

		$data['products'] = array();

		$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);
        
        if ($results) {
			foreach ($results as $result) {
			 
             	if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}
                
                
                $seller = $this->model_customerpartner_master->getPartnerIdBasedonProduct($result['product_id']);
                $seller_id = ((isset($seller['id']))?$seller['id']:0);
                
                $partner = $this->model_customerpartner_master->getProfile($seller_id);
                
                $product_info = $this->model_catalog_product->getProduct($result['product_id']);
                
                
                $data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
                    'reviews'     => $result['reviews'],
                    'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                    'sellername'  => ((isset($partner['companyname']))?$partner['companyname']:''),
					'sellerhref'  => $this->url->link('customerpartner/profile', ((isset($partner['customer_id']))?'&id=' . $partner['customer_id']:'') ,true),
                    'best_seller' => $result['best_seller'],
                    'free_shipping'=> $result['free_shipping'],
                    'front_company_name'=>$product_info['front_company_name'],
				);
			}
            
            return $this->load->view('extension/module/bestseller', $data);
		}
	}
}
