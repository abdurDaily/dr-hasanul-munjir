@foreach($fields as $key => $field)
  @php
    $value = setting($key);
    $help = ($field['emphasis'] ?? false) ? 'Wrap words in *asterisks* to show them in the italic accent font.' : ($field['type'] === 'lines' ? 'One item per line.' : null);
  @endphp
  @switch($field['type'])
    @case('textarea')
    @case('lines')
      <x-admin.textarea :name="$key" :label="$field['label']" :value="$value" :rows="$field['type'] === 'lines' ? 5 : 3" :help="$help" :required="str_contains($field['rules'], 'required')" />
      @break
    @case('html')
      <x-admin.textarea :name="$key" :label="$field['label']" :value="$value" :editor="true" rows="10" />
      @break
    @case('image')
      <x-admin.image :name="$key" :label="$field['label']" :path="$value" preset="og" />
      @if($value)<x-admin.toggle :name="'remove_'.$key" label="Remove the current image" />@endif
      @break
    @default
      <x-admin.input :name="$key" :label="$field['label']" :value="$value" :type="$field['type']" :help="$help" :required="str_contains($field['rules'], 'required')" />
  @endswitch
@endforeach
