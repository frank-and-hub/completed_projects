import React from 'react'
import { rasc } from 'utils/helper'
import Editor, {
    BtnBold,
    BtnItalic,
    Toolbar,
    createButton
} from 'react-simple-wysiwyg';

export default function Textarea({
    name,
    label,
    value,
    error,
    onChange,
    type = `text`,
    border = null,
    editor = false,
    className = null,
    required = false,
    disabled = false,
    rows = 5,
    ...props
}) {

    // Custom Buttons
    const BtnUnderline = createButton('Underline', 'U', 'underline');
    const BtnStrike = createButton('Strike', 'S̶', 'strikeThrough');
    const BtnSuperscript = createButton('Superscript', 'x²', 'superscript');
    const BtnSubscript = createButton('Subscript', 'x₂', 'subscript');

    const BtnAlignLeft = createButton('Align Left', '⇤', 'justifyLeft');
    const BtnAlignCenter = createButton('Align Center', '≡', 'justifyCenter');
    const BtnAlignRight = createButton('Align Right', '⇥', 'justifyRight');
    const BtnJustify = createButton('Justify', '≣', 'justifyFull');

    const BtnBulletList = createButton('Bullet List', '•', 'insertUnorderedList');
    const BtnNumberList = createButton('Number List', '1.', 'insertOrderedList');

    const BtnUndo = createButton('Undo', '↺', 'undo');
    const BtnRedo = createButton('Redo', '↻', 'redo');

    const BtnIndent = createButton('Indent', '→', 'indent');
    const BtnOutdent = createButton('Outdent', '←', 'outdent');
    const BtnBlockquote = createButton('Quote', '❝ ❞', 'formatBlock', '<BLOCKQUOTE>');
    const BtnHr = createButton('Horizontal Line', '─', 'insertHorizontalRule');
    const BtnCode = createButton('Code', '{ }', 'formatBlock', '<PRE>');

    // NOTE: These actions might require user interaction logic
    // const BtnInsertImage = createButton('Insert Image', '🖼', 'insertImage');
    const BtnInsertLink = createButton('Insert Link', '🔗', 'createLink');

    const editorStyles = {
        // height: className ? '100px' : '25px',
        borderRadius: '1.5rem',
    };

    const inputId = rasc(name);
    const wrapperClass = className === 'w-100' ? 'col-md-12' : 'col-md-4';
    const controlClass = `${editor ? ``: `form-control`} border-${border} ${editor ? 'tinymce-editor' : ''} ${className} ${error ? 'is-invalid' : ''} p-3`;

    const InputItem = editor ? (
    <Editor
      value={value}
      name={name}
      id={inputId}
      onChange={onChange}
      disabled={disabled}
      placeholder={`Enter ${name}`}
      style={editorStyles}
      className={controlClass}
      rows={5}
      {...props}
    >
      <Toolbar>
        {/* Text Styles */}
        <BtnBold />
        <BtnItalic />
        <BtnUnderline />
        <BtnStrike />
        <BtnSuperscript />
        <BtnSubscript />

        {/* Lists */}
        <BtnBulletList />
        <BtnNumberList />

        {/* Alignment */}
        <BtnAlignLeft />
        <BtnAlignCenter />
        <BtnAlignRight />
        <BtnJustify />

        {/* Undo/Redo */}
        <BtnUndo />
        <BtnRedo />

        {/* Structure */}
        <BtnIndent />
        <BtnOutdent />
        <BtnBlockquote />
        <BtnHr />
        <BtnCode />

        {/* Media & Links */}
        {/* <BtnInsertImage /> */}
        <BtnInsertLink />
      </Toolbar>
    </Editor>
  ) : (
    <textarea
      name={name}
      type={type}
      id={inputId}
      onChange={onChange}
      disabled={disabled}
      defaultValue={value}
      placeholder={`Enter ${name}`}
      style={editorStyles}
      className={controlClass}
      rows={rows}
      {...props}
    />
  );

    return (
        <>
            <div className={wrapperClass} >
                {label && (<label htmlFor={rasc(name)} className={`text-capitalize form-label`}>{(label)} {required && <span className={`text-danger`}>*</span>}</label>)}
                {InputItem}
                {error && <div className={`invalid-feedback`}>{error}</div>}
            </div>
        </>
    )
}