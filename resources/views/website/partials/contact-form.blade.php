<form action="{{ route('website.contact.submit') }}" method="POST" class="contact-form-card">
    @csrf
    <input type="text" name="website" value="" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
    <div class="form-grid two-columns">
        <div class="form-group"><label for="name">Your name <span>*</span></label><input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="120" placeholder="Enter your full name">@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-group"><label for="phone">Phone number <span>*</span></label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="25" placeholder="Enter mobile number">@error('phone')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="form-grid two-columns">
        <div class="form-group"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="150" placeholder="name@example.com">@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-group"><label for="service">Service</label>@php($selectedService = old('service', request('service')))<select id="service" name="service"><option value="">Choose a service</option>@foreach(['Fresh Flowers','Pooja Packets','Flower Subscription','Event Decoration','Custom Requirement'] as $service)<option value="{{ $service }}" @selected($selectedService === $service)>{{ $service }}</option>@endforeach @if($selectedService && !in_array($selectedService, ['Fresh Flowers','Pooja Packets','Flower Subscription','Event Decoration','Custom Requirement'], true))<option value="{{ $selectedService }}" selected>{{ $selectedService }}</option>@endif</select></div>
    </div>
    <div class="form-group"><label for="subject">Subject</label><input id="subject" name="subject" type="text" value="{{ old('subject') }}" maxlength="180" placeholder="How can we help?"></div>
    <div class="form-group"><label for="message">Message <span>*</span></label><textarea id="message" name="message" rows="6" required maxlength="3000" placeholder="Tell us your requirement, date, location and preferred service.">{{ old('message') }}</textarea>@error('message')<small class="field-error">{{ $message }}</small>@enderror</div>
    <button class="btn btn-primary btn-full" type="submit">Send Enquiry <span>→</span></button>
    <p class="form-note">By submitting this form, you agree that Fulawala may contact you regarding your enquiry.</p>
</form>
